import { MessageInterceptor } from "./interceptor"

export default class Message {
    actions = []
    snapshot = null
    updates = null
    calls = null
    payload = null
    responsePayload = null
    interceptors = []
    cancelled = false
    request = null
    isolate = false

    constructor(component) {
        this.component = component
    }

    addAction(action) {
        this.actions.push(action)
    }

    setInterceptors(interceptors) {
        this.interceptors = interceptors
    }

    addInterceptor(callback) {
        let interceptor = new MessageInterceptor(this, callback)

        this.interceptors.push(interceptor)

        interceptor.init()
    }

    setRequest(request) {
        this.request = request
    }

    getInterceptors() {
        return this.interceptors
    }

    cancel() {
        if (this.cancelled) return

        this.cancelled = true

        this.onCancel()

        if (this.request.hasAllCancelledMessages()) {
            this.request.abort()
        }
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

        this.rejectActionPromises('Request cancelled')

        this.onFinish()
    }

    onFailure(e) {
        this.interceptors.forEach(interceptor => interceptor.onFailure(e))

        this.rejectActionPromises('Request failed')

        this.onFinish()
    }

    onError({ response, responseBody, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onError({
            response,
            responseBody,
            preventDefault
        }))

        this.rejectActionPromises('Request failed')

        this.onFinish()
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
        let returns = this.responsePayload.effects['returns'] || []

        this.resolveActionPromises(returns)

        this.onFinish()
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

    onFinish() {
        this.interceptors.forEach(interceptor => interceptor.onFinish())
    }

    rejectActionPromises(error) {
        this.actions.forEach(action => {
            action.rejectPromise(error)
        })
    }

    resolveActionPromises(returns) {
        let resolvedActions = new Set()

        returns.forEach((value, index) => {
            let action = this.actions[index]

            if (! action) return;

            action.resolvePromise(value)

            resolvedActions.add(action)
        })

        this.actions.forEach(action => {
            if (resolvedActions.has(action)) return

            action.resolvePromise()
        })
    }
}
