import { MessageInterceptor } from "./interceptor"

export default class Message {
    actions = new Set()
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
        let actionsByFingerprint = new Map()

        Array.from(this.actions).forEach(action => {
            actionsByFingerprint.set(action.fingerprint, action)
        })

        if (actionsByFingerprint.has(action.fingerprint)) {
            actionsByFingerprint.get(action.fingerprint).addSquashedAction(action)

            return
        }

        this.actions.add(action)
    }

    getActions() {
        return Array.from(this.actions)
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

    onFailure(error) {
        this.interceptors.forEach(interceptor => interceptor.onFailure({ error }))

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
        Array.from(this.actions).forEach(action => {
            action.rejectPromise(error)
        })
    }

    resolveActionPromises(returns) {
        let resolvedActions = new Set()

        returns.forEach((value, index) => {
            let action = Array.from(this.actions)[index]

            if (! action) return;

            action.resolvePromise(value)

            resolvedActions.add(action)
        })

        Array.from(this.actions).forEach(action => {
            if (resolvedActions.has(action)) return

            action.resolvePromise()
        })
    }
}
