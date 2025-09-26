
export default class Message {
    actions = []
    promiseResolversByAction = new Map()
    snapshot = null
    updates = null
    calls = null
    payload = null
    responsePayload = null
    respondCallbacks = []
    succeedCallbacks = []
    failCallbacks = []
    interceptors = new Set()
    cancelled = false

    constructor(component) {
        this.component = component
    }

    addAction(action, promiseResolver) {
        this.actions.push(action)
        this.promiseResolversByAction.set(action, promiseResolver)
    }

    setInterceptors(interceptors) {
        this.interceptors = interceptors
    }

    cancel() {
        this.cancelled = true

        // Wait for the interceptors to finish being registered before we process the `onCancel` hooks...
        queueMicrotask(() => {
            this.onCancel()
        })
    }

    isCancelled() {
        return this.cancelled
    }

    /**
     * Lifecycle methods...
     */

    onSend() {
        this.interceptors.forEach(interceptor => interceptor.onSend({
            payload: this.payload
        }))
    }

    onCancel() {
        this.interceptors.forEach(interceptor => interceptor.onCancel())

        // Reject any promises...
        this.actions.forEach(action => {
            let promiseResolver = this.promiseResolversByAction.get(action)

            if (! promiseResolver) return;

            // promiseResolver.reject()
        })
    }

    onError(status, responseContent, preventDefault) {
        this.interceptors.forEach(interceptor => interceptor.onError({
            status,
            responseContent,
            preventDefault
        }))
    }

    onSuccess() {
        this.interceptors.forEach(interceptor => {
            interceptor.onSuccess({
                payload: this.responsePayload,
                onSync: callback => interceptor.onSync = callback,
                onMorph: callback => interceptor.onMorph = callback,
                onRender: callback => interceptor.onRender = callback
            })
        })
    }

    onSync() {
        this.interceptors.forEach(interceptor => interceptor.onSync())
    }

    onMorph() {
        this.interceptors.forEach(interceptor => interceptor.onMorph())
    }

    onRender() {
        this.interceptors.forEach(interceptor => interceptor.onRender())
    }

    /**
     * Legacy lifecycle methods...
     */

    respond() {
        this.respondCallbacks.forEach(i => i())
    }

    fail() {
        this.failCallbacks.forEach(i => i())

        // Reject any promises...
        this.actions.forEach(action => {
            let promiseResolver = this.promiseResolversByAction.get(action)

            if (! promiseResolver) return;

            promiseResolver.reject()
        })
    }

    succeed() {
        this.succeedCallbacks.forEach(i => i(this.responsePayload))

        // Process any returned values...
        let returns = this.responsePayload.effects['returns']

        if (! returns) return;

        returns.forEach((value, index) => {
            let action = this.actions[index]

            if (! action) return;

            let promiseResolver = this.promiseResolversByAction.get(action)

            if (! promiseResolver) return;

            promiseResolver.resolve(value)
        })
    }
}
