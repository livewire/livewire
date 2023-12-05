import { sendRequest } from "./request"
import { dataGet, dataSet, each, deeplyEqual, isObjecty, deepClone, diff, isObject, contentIsFromDump, splitDumpFromContent } from '@/utils'
import { on, trigger } from '@/events'

/**
 * A "commit" is anytime a Livewire component makes a server-side update.
 * Typically this is for the purposes of synchronizing state or calling
 * some action...
 */

/**
 * This bus manages the pooling of multiple commits and sending
 * those pools of commits to the server...
 */
class CommitBus {
    constructor() {
        // A list of loose, un-pooled, commits ready to be pooled and sent to the server...
        this.commits = new Set

        // A list of commit pools currently out to the server...
        this.pools = new Set
    }

    add(component) {
        // If this component already has a commit, leave it, otherwise,
        // create a new commit and add it to the list...
        let commit = this.findCommitOr(component, () => {
            let newCommit = new Commit(component)

            this.commits.add(newCommit)

            return newCommit
        })

        // Allow features like "reactive properties" to initiate associated
        // commits before those commits are pooled for a network request...
        trigger('commit.pooling', { component: commit.component })

        // Buffer the sending of a pool for 5ms to account for UI interactions
        // that will trigger multiple events within a few milliseconds of each other.
        // For example, clicking on a button that both unfocuses a field and registers a mousedown...
        bufferPoolingForFiveMs(commit, () => {
            // If this commit is already in a pool, leave it be...
            let pool = this.findPoolWithComponent(commit.component)

            if (! pool) {
                // If it's not, create a new pool or add it to an existing one and trigger a network request...
                this.createAndSendNewPool()
            }
        })

        return commit
    }

    findCommitOr(component, callback) {
        for (let [idx, commit] of this.commits.entries()) {
            if (commit.component === component) {
                return commit
            }
        }

        return callback()
    }

    findPoolWithComponent(component) {
        for (let [idx, pool] of this.pools.entries()) {
            if (pool.hasCommitFor(component)) return pool
        }
    }

    createAndSendNewPool() {
        // Split commits up across one or multiple pools to be sent as seperate network requests...
        let pools = this.corraleCommitsIntoPools()

        // Clear all commits in the queue now that they're in pools...
        this.commits.clear()

        // Go through each pool and...
        pools.forEach(pool => {
            // Add it to the list of pending pools...
            this.pools.add(pool)

            // Send it's payload along to the server...
            pool.send().then(() => {
                // When it comes back, remove it from the list...
                this.pools.delete(pool)

                // Trigger another pooling phase in case commits have
                // been added while the current request was out...
                this.sendAnyQueuedCommits()
            })
        })
    }

    corraleCommitsIntoPools() {
        let pools = []

        // Go through each commit and assess wether it should be bundled
        // with other commits or sperated into it's own pool (network request)...
        for (let [idx, commit] of this.commits.entries()) {
            let hasFoundPool = false

            // If an existing pool wants to claim a commit, let it...
            pools.forEach(pool => {
                if (pool.shouldHoldCommit(commit)) {
                    pool.add(commit)

                    hasFoundPool = true
                }
            })

            // Otherwise, create a new pool and seed it with this commit...
            if (! hasFoundPool) {
                let newPool = new RequestPool

                newPool.add(commit)

                pools.push(newPool)
            }
        }

        return pools
    }

    sendAnyQueuedCommits() {
        if (this.commits.size > 0) {
            this.createAndSendNewPool()
        }
    }
}
/**
 * This is the bus that manages pooling and sending
 * commits to the server as network requests...
 */
let commitBus = new CommitBus

/**
 * Create a commit and trigger a network request...
 */
export async function requestCommit(component) {
    let commit = commitBus.add(component)

    let promise = new Promise((resolve, reject) => {
        commit.addResolver(resolve)
    })

    promise.commit = commit

    return promise
}

/**
 * Create a commit with an "action" call and trigger a network request...
 */
export async function requestCall(component, method, params) {
    let commit = commitBus.add(component)

    let promise = new Promise((resolve, reject) => {
        commit.addCall(method, params, value => resolve(value))
    })

    promise.commit = commit

    return promise
}

/**
 * The RequestPool contains a list of commits to be sent to the server...
 */
class RequestPool {
    constructor() {
        this.commits = new Set
    }

    add(commit) {
        this.commits.add(commit)
    }

    hasCommitFor(component) {
        // Determine if this pool already has a commit for this component...
        for (let [idx, commit] of this.commits.entries()) {
            if (commit.component === component) return true
        }

        return false
    }

    // Determine if a commit should be added to this pool or isolated into its own...
    shouldHoldCommit(commit) {
        return ! commit.isolate
    }

    async send() {
        this.prepare()

        // Send this pool of commits to the server and let the commits
        // Manage their own response actions...
        await sendRequest(this)
    }

    prepare() {
        // Give each commit a chance to do any last-minute prep
        // before being sent to the server.
        this.commits.forEach(i => i.prepare())
    }

    payload() {
        // Extract a request payload from each of the commits in this pool...
        let commitPayloads = []

        // Collect success and failure callbacks to be used inside aggregated callbacks...
        let successReceivers = []
        let failureReceivers = []

        this.commits.forEach(commit => {
            let [payload, succeed, fail] = commit.toRequestPayload()

            commitPayloads.push(payload)
            successReceivers.push(succeed)
            failureReceivers.push(fail)
        })

        // Aggregate the success and failure callbacks for individual commits
        // into something that can be called singularly...
        let succeed = components => successReceivers.forEach(receiver => receiver(components.shift()))

        let fail = () => failureReceivers.forEach(receiver => receiver())

        return [ commitPayloads, succeed, fail ]
    }
}

/**
 * A commit represents an individual component updating itself server-side...
 */
class Commit {
    constructor(component) {
        this.component = component
        this.isolate = false
        this.calls = []
        this.receivers = []
        this.resolvers = []
    }

    // Add a new resolver to be resolved when a commit is returned from the server...
    addResolver(resolver) {
        this.resolvers.push(resolver)
    }

    // Add a new action "call" to the commit payload...
    addCall(method, params, receiver) {
        this.calls.push({
            path: '', method, params,
            handleReturn(value) {
                receiver(value)
            },
        })
    }

    prepare() {
        trigger('commit.prepare', { component: this.component })
    }

    // Generate a JSON-friendly server-request payload...
    toRequestPayload() {
        // Generate a "diff" of the current last known server-side state, and
        // the new front-end state so that we can update the server atomically...
        let propertiesDiff = diff(this.component.canonical, this.component.ephemeral)

        let payload = {
            snapshot: this.component.snapshotEncoded,
            updates: propertiesDiff,
            calls: this.calls.map(i => ({
                path: i.path,
                method: i.method,
                params: i.params,
            }))
        }

        // Store success and failure hooks from commit listeners
        // so they can be aggregated into a singular callback later...
        let succeedCallbacks = []
        let failCallbacks = []
        let respondCallbacks = []

        let succeed = (fwd) => succeedCallbacks.forEach(i => i(fwd))
        let fail = () => failCallbacks.forEach(i => i())
        let respond = () => respondCallbacks.forEach(i => i())

        // Allow other areas of the codebase to hook into the lifecycle
        // of an individual commit...
        let finishTarget = trigger('commit', {
            component: this.component,
            commit: payload,
            succeed: (callback) => {
                succeedCallbacks.push(callback)
            },
            fail: (callback) => {
                failCallbacks.push(callback)
            },
            respond: (callback) => {
                respondCallbacks.push(callback)
            },
        })

        // Handle the response payload for a commit...
        let handleResponse = (response) => {
            let { snapshot, effects } = response

            respond()

            // Take the new snapshot and merge it into the existing one...
            this.component.mergeNewSnapshot(snapshot, effects, propertiesDiff)

            // Trigger any side effects from the payload like "morph" and "dispatch event"...
            processEffects(this.component, this.component.effects)

            if (effects['returns']) {
                let returns = effects['returns']

                // Here we'll match up returned values with their method call handlers. We need to build up
                // two "stacks" of the same length and walk through them together to handle them properly...
                let returnHandlerStack = this.calls.map(({ handleReturn }) => (handleReturn))

                returnHandlerStack.forEach((handleReturn, index) => {
                    handleReturn(returns[index])
                })
            }

            let parsedSnapshot = JSON.parse(snapshot)

            finishTarget({ snapshot: parsedSnapshot, effects })

            this.resolvers.forEach(i => i())

            succeed(response)
        }

        let handleFailure = () => {
            respond()

            fail()
        }

        return [payload, handleResponse, handleFailure]
    }
}

/**
 * Here we'll take the new state and side effects from the
 * server and use them to update the existing data that
 * users interact with, triggering reactive effects.
 */
export function processEffects(target, effects) {
    trigger('effects', target, effects)
}

let buffersByCommit = new WeakMap

function bufferPoolingForFiveMs(commit, callback) {
    if (buffersByCommit.has(commit)) return

    buffersByCommit.set(commit, setTimeout(() => {
        callback()

        buffersByCommit.delete(commit)
    }, 5))
}
