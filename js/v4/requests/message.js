import { trigger } from '@/hooks'
import { morph } from '@/morph'

export default class Message {
    updates = {}
    actions = []
    payload = {}
    context = {}
    interceptors = new Set()
    resolvers = []
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

    addInterceptor(interceptor) {
        interceptor.cancel = () => this.cancel()

        this.interceptors.add(interceptor)
    }

    addContext(key, value) {
        if (! this.context[key]) {
            this.context[key] = []
        }

        if (this.context[key].includes(value)) return

        this.context[key].push(value)
    }

    addAction(method, params, resolve) {
        // If the action isn't a magic action then it supersedes any magic actions.
        // Remove them so there aren't any unnecessary actions in the request...
        if (! this.isMagicAction(method)) {
            this.removeAllMagicActions()
        }

        if (this.isMagicAction(method)) {
            // If the action is a magic action and it already exists then remove the 
            // old action so there aren't any duplicate actions in the request...
            this.findAndRemoveAction(method)

            this.actions.push({
                method: method,
                params: params,
                handleReturn: () => {},
            })

            // We need to store the resolver, so we can call all of the 
            // magic action resolvers when the message is finished...
            this.resolvers.push(resolve)

            return
        }

        this.actions.push({
            method: method,
            params: params,
            handleReturn: resolve,
        })
    }

    magicActions () {
        return [
            '$refresh',
            '$set',
            '$sync',
        ]
    }

    isMagicAction(method) {
        return this.magicActions().includes(method)
    }

    removeAllMagicActions() {
        this.actions = this.actions.filter(i => !this.isMagicAction(i.method))
    }

    findAndRemoveAction(method) {
        this.actions = this.actions.filter(i => i.method !== method)
    }

    cancelIfItShouldBeCancelled() {
        if (this.isSucceeded()) return

        this.cancel()
    }

    buffer() {
        this.status = 'buffering'
    }

    prepare() {
        trigger('message.prepare', { component: this.component })

        this.status = 'preparing'

        this.updates = this.component.getUpdates()

        let snapshot = this.component.getEncodedSnapshotWithLatestChildrenMergedIn()

        this.payload = {
            snapshot: snapshot,
            updates: this.updates,
            // @todo: Rename to "actions"...
            calls: this.actions.map(i => ({
                method: i.method,
                params: i.params,
            })),
            context: this.context,
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

        this.beforeSend()
    }

    beforeSend() {
        this.interceptors.forEach(i => i.beforeSend({ component: this.component, payload: this.payload }))
    }

    afterSend() {
        this.interceptors.forEach(i => i.afterSend({ component: this.component, payload: this.payload }))
    }

    beforeResponse(response) {
        this.interceptors.forEach(i => i.beforeResponse({ component: this.component, response }))
    }

    afterResponse(response) {
        this.interceptors.forEach(i => i.afterResponse({ component: this.component, response }))
    }

    respond() {
        this.respondCallbacks.forEach(i => i())
    }

    succeed(response) {
        if (this.isCancelled()) return

        this.status = 'succeeded'

        this.beforeResponse(response)

        this.respond()

        let { snapshot, effects } = response

        this.component.mergeNewSnapshot(snapshot, effects, this.updates)

        this.afterResponse(response)

        // Trigger any side effects from the payload like "morph" and "dispatch event"...
        this.component.processEffects(this.component.effects)

        this.resolvers.forEach(i => i())

        if (effects['returns']) {
            let returns = effects['returns']

            // Here we'll match up returned values with their method call handlers. We need to build up
            // two "stacks" of the same length and walk through them together to handle them properly...
            let returnHandlerStack = this.actions.map(({ handleReturn }) => (handleReturn))

            returnHandlerStack.forEach((handleReturn, index) => {
                handleReturn(returns[index])
            })
        }

        let parsedSnapshot = JSON.parse(snapshot)

        this.finishTarget({ snapshot: parsedSnapshot, effects })

        this.interceptors.forEach(i => i.onSuccess(response))

        this.succeedCallbacks.forEach(i => i(response))

        let html = effects['html']

        if (! html) return

        this.interceptors.forEach(i => i.beforeRender({ component: this.component }))

        queueMicrotask(() => {
            this.interceptors.forEach(i => i.beforeMorph({ component: this.component, el: this.component.el, html }))

            morph(this.component, this.component.el, html)

            this.interceptors.forEach(i => i.afterMorph({ component: this.component, el: this.component.el, html }))

            setTimeout(() => {
                this.interceptors.forEach(i => i.afterRender({ component: this.component }))
            })
        })
    }

    error(e) {
        if (this.isCancelled()) return

        this.status = 'errored'

        this.interceptors.forEach(i => i.onError(e))
    }

    fail(response, content) {
        if (this.isCancelled()) return

        this.status = 'failed'

        this.respond()

        this.interceptors.forEach(i => i.onError(response, content))

        this.failCallbacks.forEach(i => i())
    }

    cancel() {
        this.status = 'cancelled'

        this.interceptors.forEach(i => i.onCancel())

        // @todo: Get this working with `wire:loading`...
        // this.respond()
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

    isErrored() {
        return this.status === 'errored'
    }

    isFailed() {
        return this.status === 'failed'
    }

    isFinished() {
        return this.isSucceeded() || this.isCancelled() || this.isFailed() || this.isErrored()
    }
}
