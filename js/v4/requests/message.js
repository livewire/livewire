import { trigger } from '@/hooks'

export default class Message {
    updates = {}
    calls = []
    payload = {}
    status = 'waiting'
    succeedCallbacks = []
    failCallbacks = []
    respondCallbacks = []
    finishTarget = null
    request = null
    isolate = false

    constructor(component) {
        this.component = component
    }

    addCall(method, params, handleReturn) {
        this.calls.push({
            method: method,
            params: params,
            handleReturn,
        })
    }

    cancelIfItShouldBeCancelled() {
        if (this.isSucceeded()) return

        this.cancel()
    }

    buffer() {
        this.status = 'buffering'
    }

    prepare() {
        this.status = 'preparing'

        this.updates = this.component.getUpdates()

        let snapshot = this.component.getEncodedSnapshotWithLatestChildrenMergedIn()

        this.payload = {
            snapshot: snapshot,
            updates: this.updates,
            calls: this.calls.map(i => ({
                method: i.method,
                params: i.params,
            }))
        }

        // Allow other areas of the codebase to hook into the lifecycle
        // of an individual commit...
        this.finishTarget = trigger('commit', {
            component: this.component,
            commit: this.payload,
            succeed: (callback) => {
                this.succeedCallbacks.push(callback)
            },
            fail: (callback) => {
                this.failCallbacks.push(callback)
            },
            respond: (callback) => {
                this.respondCallbacks.push(callback)
            },
        })
    }

    respond() {
        this.respondCallbacks.forEach(i => i())
    }

    succeed(response) {
        if (this.isCancelled()) return

        this.status = 'succeeded'

        this.respond()

        let { snapshot, effects } = response

        this.component.mergeNewSnapshot(snapshot, effects, this.updates)

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

        this.finishTarget({ snapshot: parsedSnapshot, effects })

        this.succeedCallbacks.forEach(i => i(response))
    }

    fail() {
        this.status = 'failed'

        this.respond()

        this.failCallbacks.forEach(i => i())
    }

    cancel() {
        this.status = 'cancelled'
    }

    isBuffering() {
        return this.status === 'buffering'
    }

    isPreparing() {
        return this.status === 'preparing'
    }

    isSucceeded() {
        return this.status === 'succeeded'
    }

    isCancelled() {
        return this.status === 'cancelled'
    }

    isFailed() {
        return this.status === 'failed'
    }

    isFinished() {
        return this.isSucceeded() || this.isCancelled() || this.isFailed()
    }
}
