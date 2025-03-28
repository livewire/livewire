import { diff } from '@/utils'
import { on, trigger } from '@/hooks'

/**
 * A commit represents an individual component updating itself server-side...
 */
export class Commit {
    constructor(component) {
        this.component = component
        this.isolate = false
        this.interruptible = false
        this.interrupted = false
        this.silentInterruption = true // Default to silent interruptions for framework features
        this.calls = []
        this.receivers = []
        this.resolvers = []
        this.rejectors = []
    }

    // Add a new resolver to be resolved when a commit is returned from the server...
    addResolver(resolver, rejector) {
        this.resolvers.push(resolver)
        this.rejectors.push(rejector)
    }

    // Add a new action "call" to the commit payload...
    addCall(method, params, resolver, rejector) {
        this.calls.push({
            path: '', method, params,
            handleReturn(value) {
                resolver(value)
            },
            handleReject(error) {
                rejector(error)
            }
        })
    }

    // Handle interruption by rejecting all promises
    handleInterruption() {
        this.interrupted = true

        // Create a custom error for interruptions
        const error = new Error('Request was interrupted by a newer request')
        error.name = 'InterruptedException'

        if (!this.silentInterruption) {
            // Only reject promises if silent interruption is disabled
            // Reject all promises using their reject functions
            this.rejectors.forEach(reject => {
                try {
                    reject(error)
                } catch (e) {
                    console.error('Error rejecting promise:', e)
                }
            })

            // Reject all call promises
            this.calls.forEach(call => {
                if (call.handleReject) {
                    try {
                        call.handleReject(error)
                    } catch (e) {
                        console.error('Error rejecting call promise:', e)
                    }
                }
            })
        }

        // Clear the arrays since we've handled them
        this.resolvers = []
        this.rejectors = []
        this.calls = []
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
            // If this commit has been interrupted, don't process the response
            if (this.interrupted) {
                return
            }

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

            // Clean up after successful response
            this.resolvers = []
            this.rejectors = []
            this.calls = []
        }

        let handleFailure = () => {
            respond()

            // Create a failure error object
            const error = new Error('Request failed')
            error.name = 'RequestFailedException'

            // Reject all promises
            this.rejectors.forEach(reject => {
                try {
                    reject(error)
                } catch (e) {
                    console.error('Error rejecting promise on failure:', e)
                }
            })

            // Reject all call promises
            this.calls.forEach(call => {
                if (call.handleReject) {
                    try {
                        call.handleReject(error)
                    } catch (e) {
                        console.error('Error rejecting call promise on failure:', e)
                    }
                }
            })

            // Clean up after failure
            this.resolvers = []
            this.rejectors = []
            this.calls = []

            fail()
        }

        return [payload, handleResponse, handleFailure]
    }
}
