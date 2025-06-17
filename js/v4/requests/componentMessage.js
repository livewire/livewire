import { trigger } from '@/hooks'

export default class ComponentMessage {
    calls = []
    payload = {}
    status = 'waiting'
    resolvers = []
    succeedCallbacks = []
    failCallbacks = []
    respondCallbacks = []
    finishTarget = null
    request = null

    constructor(component) {
        this.component = component
    }

    addCall(method, params) {
        this.calls.push({
            method: method,
            params: params,
        })
    }

    addResolver(resolver) {
        this.resolvers.push(resolver)
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

        let updates = this.component.getUpdates()

        let snapshot = this.component.getEncodedSnapshotWithLatestChildrenMergedIn()

        this.payload = {
            snapshot: snapshot,
            updates: updates,
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

    succeed(response) {
        if (this.isCancelled()) return
        
        this.status = 'succeeded'
        
        let { snapshot, effects } = response

        this.component.mergeNewSnapshot(snapshot, effects, this.updates)

        this.component.processEffects(this.component.effects)

        let parsedSnapshot = JSON.parse(snapshot)

        this.finishTarget({ snapshot: parsedSnapshot, effects })

        this.resolvers.forEach(i => i())

        this.succeedCallbacks.forEach(i => i(response))
    }

    cancel() {
        this.status = 'cancelled'

        this.request?.cancelIfItShouldBeCancelled()
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

    isFinished() {
        return this.isSucceeded() || this.isCancelled()
    }
}