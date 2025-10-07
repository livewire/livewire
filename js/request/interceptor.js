import { WeakBag } from "@/utils"

export class MessageInterceptor {
    onSend = () => {}
    onCancel = () => {}
    onFailure = () => {}
    onError = () => {}
    onStream = () => {}
    onSuccess = () => {}
    onFinish = () => {}
    onSync = () => {}
    onEffect = () => {}
    onMorph = () => {}
    onRender = () => {}

    hasBeenSynchronouslyCancelled = false

    constructor(message, callback) {
        this.message = message
        this.callback = callback

        let isInsideCallbackSynchronously = true

        this.callback({
            message: this.message,
            actions: this.message.actions,
            component: this.message.component,
            onSend: (callback) => this.onSend = callback,
            onCancel: (callback) => this.onCancel = callback,
            onFailure: (callback) => this.onFailure = callback,
            onError: (callback) => this.onError = callback,
            onStream: (callback) => this.onStream = callback,
            onSuccess: (callback) => this.onSuccess = callback,
            onFinish: (callback) => this.onFinish = callback,
            cancel: () => {
                if (isInsideCallbackSynchronously) {
                    this.hasBeenSynchronouslyCancelled = true
                } else {
                    this.message.cancel()
                }
            },
        })

        isInsideCallbackSynchronously = false
    }

    init() {
        if (this.hasBeenSynchronouslyCancelled) {
            this.message.cancel()
        }
    }
}

export class RequestInterceptor {
    onSend = () => {}
    onAbort = () => {}
    onFailure = () => {}
    onResponse = () => {}
    onParsed = () => {}
    onError = () => {}
    onStream = () => {}
    onRedirect = () => {}
    onDump = () => {}
    onSuccess = () => {}

    hasBeenSynchronouslyAborted = false

    constructor(request, callback) {
        this.request = request

        this.callback = callback

        let isInsideCallbackSynchronously = true

        this.callback({
            request: this.request,
            onSend: (callback) => this.onSend = callback,
            onAbort: (callback) => this.onAbort = callback,
            onFailure: (callback) => this.onFailure = callback,
            onResponse: (callback) => this.onResponse = callback,
            onParsed: (callback) => this.onParsed = callback,
            onError: (callback) => this.onError = callback,
            onStream: (callback) => this.onStream = callback,
            onRedirect: (callback) => this.onRedirect = callback,
            onDump: (callback) => this.onDump = callback,
            onSuccess: (callback) => this.onSuccess = callback,
            abort: () => {
                if (isInsideCallbackSynchronously) {
                    this.hasBeenSynchronouslyAborted = true
                } else {
                    this.request.abort()
                }
            },
        })

        isInsideCallbackSynchronously = false
    }

    init() {
        if (this.hasBeenSynchronouslyAborted) {
            this.request.abort()
        }
    }
}

export class InterceptorRegistry {
    messageInterceptorCallbacks = []
    messageInterceptorCallbacksByComponent = new WeakBag
    requestInterceptorCallbacks = []

    addInterceptor(component, callback) {
        this.messageInterceptorCallbacksByComponent.add(component, callback)

        return () => {
            this.messageInterceptorCallbacksByComponent.delete(component, callback)
        }
    }

    addMessageInterceptor(callback) {
        this.messageInterceptorCallbacks.push(callback)

        return () => {
            this.messageInterceptorCallbacks.splice(this.messageInterceptorCallbacks.indexOf(callback), 1)
        }
    }

    addRequestInterceptor(callback) {
        this.requestInterceptorCallbacks.push(callback)

        return () => {
            this.requestInterceptorCallbacks.splice(this.requestInterceptorCallbacks.indexOf(callback), 1)
        }
    }

    getMessageInterceptors(message) {
        let callbacks = [
            ...this.messageInterceptorCallbacksByComponent.get(message.component),
            ...this.messageInterceptorCallbacks,
        ]

        return callbacks.map(callback => {
            return new MessageInterceptor(message, callback)
        })
    }

    getRequestInterceptors(request) {
        return this.requestInterceptorCallbacks.map(callback => {
            return new RequestInterceptor(request, callback)
        })
    }
}