import { WeakBag } from "@/utils"

export class Interceptor {
    onSend = () => {}
    onCancel = () => {}
    onError = () => {}
    onSuccess = () => {}
    onSync = () => {}
    onMorph = () => {}
    onRender = () => {}

    constructor(message, callback) {
        this.message = message

        callback({
            actions: message.actions,
            component: message.component,
            onSend: (callback) => this.onSend = callback,
            onCancel: (callback) => this.onCancel = callback,
            onError: (callback) => this.onError = callback,
            onSuccess: (callback) => this.onSuccess = callback,
            cancel: () => this.message.cancel()
        })
    }
}

export class InterceptorRegistry {
    interceptorCallbacksByComponent = new WeakBag
    interceptorsByComponent = new WeakBag

    add(component, callback) {
        this.interceptorCallbacksByComponent.add(component, callback)
    }

    getRelevantInterceptors(message) {
        let interceptorCallbacks = this.interceptorCallbacksByComponent.get(message.component)

        return interceptorCallbacks.map(callback => {
            return new Interceptor(message, callback)
        })
    }
}