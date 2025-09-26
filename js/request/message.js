
export default class Message {
    actions = []
    promiseResolversByAction = new Map()
    snapshot = null
    updates = null
    calls = null
    payload = null
    responsePayload = null
    interceptors = []
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

    getInterceptors() {
        return this.interceptors
    }

    cancel() {
        if (this.cancelled) return

        this.cancelled = true

        this.onCancel()
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

        this.resolvePromises()
    }

    onFailure(e) {
        this.interceptors.forEach(interceptor => interceptor.onFailure(e))

        this.resolvePromises()
    }

    onError({ response, responseBody, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onError({
            response,
            responseBody,
            preventDefault
        }))

        this.resolvePromises()
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

    onSync() {
        this.interceptors.forEach(interceptor => interceptor.onSync())
    }

    onMorph() {
        this.interceptors.forEach(interceptor => interceptor.onMorph())
    }

    onRender() {
        this.interceptors.forEach(interceptor => interceptor.onRender())
    }

    resolvePromises() {
        this.actions.forEach(action => {
            let promiseResolver = this.promiseResolversByAction.get(action)

            if (! promiseResolver) return;

            promiseResolver.resolve()
        })
    }
}
