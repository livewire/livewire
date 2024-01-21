import { diff } from '@/utils'
import { on, trigger } from '@/hooks'

/**
 * A commit represents an individual component updating itself server-side...
 */
export class Commit {
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

        let updates = this.component.mergeQueuedUpdates(propertiesDiff)

        let payload = {
            snapshot: this.component.snapshotEncoded,
            updates: updates,
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
            this.component.mergeNewSnapshot(snapshot, effects, updates)

            // Trigger any side effects from the payload like "morph" and "dispatch event"...
            this.component.processEffects(this.component.effects)

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
