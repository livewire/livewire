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
    onEffect = async () => {}
    onMorph = async () => {}
    onRender = () => {}

    constructor(message, callback) {
        this.message = message
        this.callback = callback

        this.callback({
            message: this.message,
            cancel: () => {
                // If we're not yet attached to the message's interceptors,
                // call our own onCancel since message.invokeOnCancel() won't reach us
                let attachedToMessage = this.message.getInterceptors().includes(this)

                if (!attachedToMessage) {
                    this.onCancel()
                }

                this.message.cancel()
            },
            onSend: (callback) => this.onSend = callback,
            onCancel: (callback) => this.onCancel = callback,
            onFailure: (callback) => this.onFailure = callback,
            onError: (callback) => this.onError = callback,
            onStream: (callback) => this.onStream = callback,
            onSuccess: (callback) => this.onSuccess = callback,
            onFinish: (callback) => this.onFinish = callback,
        })
    }

    init() {
        // Reserved for future use
    }
}

export class RequestInterceptor {
    onSend = () => {}
    onCancel = () => {}
    onFailure = () => {}
    onResponse = () => {}
    onParsed = () => {}
    onError = () => {}
    onStream = () => {}
    onRedirect = () => {}
    onDump = () => {}
    onSuccess = () => {}
    onFinish = () => {}

    constructor(request, callback) {
        this.request = request
        this.callback = callback

        this.callback({
            request: this.request,
            onSend: (callback) => this.onSend = callback,
            onCancel: (callback) => this.onCancel = callback,
            onFailure: (callback) => this.onFailure = callback,
            onResponse: (callback) => this.onResponse = callback,
            onParsed: (callback) => this.onParsed = callback,
            onError: (callback) => this.onError = callback,
            onStream: (callback) => this.onStream = callback,
            onRedirect: (callback) => this.onRedirect = callback,
            onDump: (callback) => this.onDump = callback,
            onSuccess: (callback) => this.onSuccess = callback,
            onFinish: (callback) => this.onFinish = callback,
        })
    }

    init() {
        // Reserved for future use
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