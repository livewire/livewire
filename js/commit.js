import { triggerSend, waitUntilTheCurrentRequestIsFinished } from "./request"
import { dataGet, dataSet, each, deeplyEqual, isObjecty, deepClone, diff, isObject, contentIsFromDump, splitDumpFromContent } from '@/utils'
import { on, trigger } from '@/events'

/**
 * A "commit" is anytime a Livewire component makes a server-side update.
 * Typically this is for the purposes of synchronizing state or calling
 * some action...
 */

// The running queue of component commits to send to the server when the time comes...
let commitQueue = []

export function getCommits() {
    return commitQueue
}

export function flushCommits(callback) {
    while (commitQueue.length > 0) {
        callback(commitQueue.shift())
    }
}

function findOrCreateCommit(component) {
    let commit = commitQueue.find(i => {
        return i.component.id === component.id
    })

    if (! commit) {
        commitQueue.push(commit = new Commit(component))
    }

    return commit
}

export async function requestCommit(component) {
    return await waitUntilTheCurrentRequestIsFinished(() => {
        let commit = findOrCreateCommit(component)

        triggerSend()

        return new Promise((resolve, reject) => {
            commit.addResolver(resolve)
        })
    })
}

export async function requestCall(component, method, params) {
    return await waitUntilTheCurrentRequestIsFinished(() => {
        let commit = findOrCreateCommit(component)

        triggerSend()

        return new Promise((resolve, reject) => {
            commit.addCall(method, params, value => resolve(value))
        })
    })
}

/**
 * The term "commit" here refers to anytime we're making a network
 * request, updating the server, and generating a new snapshot.
 * We're "requesting" a new commit rather than executing it
 * immediately, because we might want to batch multiple
 * simultaneus commits from other livewire targets.
 */
class Commit {
    constructor(component) {
        this.component = component
        this.calls = []
        this.receivers = []
        this.resolvers = []
    }

    addResolver(resolver) {
        this.resolvers.push(resolver)
    }

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

    toRequestPayload() {
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

        let succeedCallbacks = []
        let failCallbacks = []
        let respondCallbacks = []

        let succeed = (fwd) => succeedCallbacks.forEach(i => i(fwd))
        let fail = () => failCallbacks.forEach(i => i())
        let respond = () => respondCallbacks.forEach(i => i())

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

        let handleResponse = (response) => {
            let { snapshot, effects } = response

            respond()

            this.component.mergeNewSnapshot(snapshot, effects, propertiesDiff)

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
