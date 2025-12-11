
export default class Action {
    squashedActions = new Set()

    // Interceptor callbacks
    onSendCallbacks = []
    onSuccessCallbacks = []
    onErrorCallbacks = []
    onFailureCallbacks = []
    onFinishCallbacks = []

    // Reference to the message this action belongs to (set by message.addAction)
    message = null
    cancelled = false
    deferred = false

    // Set by constructAction() to avoid circular dependency
    _fire = null

    constructor(component, name, params = [], metadata = {}, origin = null) {
        this.component = component
        this.name = name
        this.params = params
        this.metadata = metadata
        this.origin = origin

        this.promise = new Promise((resolve, reject) => {
            this.promiseResolution = { resolve, reject }
        })
    }

    cancel() {
        if (this.cancelled) return

        this.cancelled = true

        this.invokeOnFinish()
        this.rejectPromise({ status: null, body: null, json: null, errors: null })

        // Also cancel squashed actions
        this.squashedActions.forEach(action => action.cancel())

        // Remove from message if attached
        if (this.message) {
            this.message.removeAction(this)
        }
    }

    isCancelled() {
        return this.cancelled
    }

    defer() {
        this.deferred = true
    }

    isDeferred() {
        return this.deferred
    }

    fire() {
        if (this._fire) {
            this._fire(this)
        }
    }

    get fingerprint() {
        let componentId = this.component.id
        let name = this.name
        let params = JSON.stringify(this.params)
        let metadata = JSON.stringify(this.metadata)

        // btoa only supports Latin-1 characters, not UTF-8, so we need to encode the string first...
        return window.btoa(String.fromCharCode(...new TextEncoder().encode(componentId + name + params + metadata)))
    }

    isAsync() {
        let asyncMethods = this.component.snapshot.memo?.async || []

        let methodIsMarkedAsync = asyncMethods.includes(this.name)

        let actionIsAsync = this.origin?.directive?.modifiers.includes('async') || (!! this.metadata.async)

        return methodIsMarkedAsync || actionIsAsync
    }

    isJson() {
        let jsonMethods = this.component.snapshot.memo?.json || []

        return jsonMethods.includes(this.name)
    }

    addInterceptor(callback) {
        callback({
            action: this,
            onSend: (cb) => this.onSendCallbacks.push(cb),
            onSuccess: (cb) => this.onSuccessCallbacks.push(cb),
            onError: (cb) => this.onErrorCallbacks.push(cb),
            onFailure: (cb) => this.onFailureCallbacks.push(cb),
            onFinish: (cb) => this.onFinishCallbacks.push(cb),
        })
    }

    // Lifecycle invocations
    invokeOnSend({ call }) {
        this.onSendCallbacks.forEach(cb => cb({ call }))
        this.squashedActions.forEach(action => action.invokeOnSend({ call }))
    }

    invokeOnSuccess(result) {
        this.onSuccessCallbacks.forEach(cb => cb(result))
        this.squashedActions.forEach(action => action.invokeOnSuccess(result))
    }

    invokeOnError({ response, body }) {
        this.onErrorCallbacks.forEach(cb => cb({ response, body }))
        this.squashedActions.forEach(action => action.invokeOnError({ response, body }))
    }

    invokeOnFailure({ error }) {
        this.onFailureCallbacks.forEach(cb => cb({ error }))
        this.squashedActions.forEach(action => action.invokeOnFailure({ error }))
    }

    invokeOnFinish() {
        this.onFinishCallbacks.forEach(cb => cb())
        this.squashedActions.forEach(action => action.invokeOnFinish())
    }

    mergeMetadata(metadata) {
        this.metadata = { ...this.metadata, ...metadata }
    }

    rejectPromise(error) {
        // Resolving instead of rejecting to avoid unhandled promise rejection errors...
        // Should think about how we can handle this better...
        this.squashedActions.forEach(action => action.rejectPromise(error))

        this.promiseResolution.reject(error)
    }

    addSquashedAction(action) {
        this.squashedActions.add(action)
    }

    resolvePromise(value) {
        this.squashedActions.forEach(action => action.resolvePromise(value))
        this.promiseResolution.resolve(value)
    }
}
