import { MessageInterceptor } from "./interceptor"

export default class Message {
    actions = new Set()
    snapshot = null
    updates = null
    calls = null
    payload = null
    responsePayload = null
    pendingReturns = []
    pendingReturnsMeta = {}
    interceptors = []
    cancelled = false
    request = null
    _scope = null

    // Ensure scope isn't accessed until it's been set...
    get scope() {
        if (! this._scope) {
            throw new Error('Message scope has not been set yet')
        }

        return this._scope
    }

    set scope(scope) {
        this._scope = scope
    }

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

        action.message = this
        this.actions.add(action)
    }

    removeAction(action) {
        this.actions.delete(action)
        action.message = null

        // If no actions remain, cancel the message
        if (this.actions.size === 0) {
            this.cancel()
        }
    }

    getActions() {
        return Array.from(this.actions)
    }

    hasActionForIsland(island) {
        return this.getActions().some(action => {
            return action.metadata.island?.name === island.metadata.name
        })
    }

    hasActionForComponent() {
        return this.getActions().some(action => {
            return action.metadata.island === undefined
        })
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

        this.invokeOnCancel()

        if (this.request?.hasAllCancelledMessages()) {
            this.request.cancel()
        }
    }

    isCancelled() {
        return this.cancelled
    }

    isAsync() {
        return Array.from(this.actions).every(action => action.isAsync())
    }

    /**
     * Lifecycle methods...
     */

    invokeOnSend() {
        this.interceptors.forEach(interceptor => interceptor.onSend({
            payload: this.payload
        }))

        // Invoke action-level onSend callbacks
        Array.from(this.actions).forEach((action, index) => {
            let call = this.calls[index]
            action.invokeOnSend({ call })
        })
    }

    invokeOnCancel() {
        this.interceptors.forEach(interceptor => interceptor.onCancel())

        this.rejectActionPromises({ status: null, body: null, json: null, errors: null })

        // Invoke action-level onFinish callbacks
        Array.from(this.actions).forEach(action => action.invokeOnFinish())

        this.invokeOnFinish()
    }

    invokeOnFailure(error) {
        this.interceptors.forEach(interceptor => interceptor.onFailure({ error }))

        // Invoke action-level onFailure callbacks
        Array.from(this.actions).forEach(action => action.invokeOnFailure({ error }))

        this.rejectActionPromises({ status: null, body: null, json: null, errors: null })

        // Invoke action-level onFinish callbacks
        Array.from(this.actions).forEach(action => action.invokeOnFinish())

        this.invokeOnFinish()
    }

    invokeOnError({ response, body, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onError({
            response,
            body,
            preventDefault
        }))

        // Invoke action-level onError callbacks
        Array.from(this.actions).forEach(action => action.invokeOnError({ response, body, preventDefault }))

        // Try to parse body as JSON for the rejection payload
        let json = null
        try { json = JSON.parse(body) } catch (e) {}

        this.rejectActionPromises({ status: response.status, body, json, errors: null })

        // Invoke action-level onFinish callbacks
        Array.from(this.actions).forEach(action => action.invokeOnFinish())

        this.invokeOnFinish()
    }

    invokeOnStream({ json }) {
        this.interceptors.forEach(interceptor => interceptor.onStream({ json }))
    }

    invokeOnSuccess() {
        this.interceptors.forEach(interceptor => {
            interceptor.onSuccess({
                payload: this.responsePayload,
                onSync: callback => interceptor.onSync = callback,
                onEffect: callback => interceptor.onEffect = callback,
                onMorph: callback => interceptor.onMorph = callback,
                onRender: callback => interceptor.onRender = callback
            })
        })

        // Store returns for later resolution (after morph)
        this.pendingReturns = this.responsePayload.effects['returns'] || []
        this.pendingReturnsMeta = this.responsePayload.effects['returnsMeta'] || {}
    }

    invokeOnSync() {
        this.interceptors.forEach(interceptor => interceptor.onSync())
    }

    invokeOnEffect() {
        this.interceptors.forEach(interceptor => interceptor.onEffect())
    }

    async invokeOnMorph() {
        await Promise.all(
            this.interceptors.map(interceptor => interceptor.onMorph())
        )
    }

    invokeOnRender() {
        this.interceptors.forEach(interceptor => interceptor.onRender())
    }

    invokeOnFinish() {
        this.interceptors.forEach(interceptor => interceptor.onFinish())
    }

    rejectActionPromises({ status, body, json, errors }) {
        Array.from(this.actions).forEach(action => {
            action.rejectPromise({ status, body, json, errors })
        })
    }

    resolveActionPromises(returns, returnsMeta) {
        let resolvedActions = new Set()

        returns.forEach((value, index) => {
            let action = Array.from(this.actions)[index]

            if (! action) return;

            // Check for validation errors in returnsMeta
            let meta = returnsMeta[index]
            if (meta?.errors) {
                action.rejectPromise({ status: 422, body: null, json: null, errors: meta.errors })
                action.invokeOnFinish()
                resolvedActions.add(action)
                return
            }

            // Invoke action-level onSuccess callback with the return value
            action.invokeOnSuccess(value)

            action.resolvePromise(value)

            // Invoke action-level onFinish callback
            action.invokeOnFinish()

            resolvedActions.add(action)
        })

        Array.from(this.actions).forEach(action => {
            if (resolvedActions.has(action)) return

            // Invoke action-level onSuccess callback (undefined return)
            action.invokeOnSuccess(undefined)

            action.resolvePromise()

            // Invoke action-level onFinish callback
            action.invokeOnFinish()
        })
    }
}
