class Interceptor {
    callbacks = {
        default: () => {},
        fire: () => {},
        request: () => {},
        beforeResponse: () => {},
        response: () => {},
        success: () => {},
        error: () => {},
        cancel: () => {},
        beforeMorph: () => {},
        afterMorph: () => {},
    }

    constructor(callback, method) {
        this.callbacks.default = callback
        this.method = method
    }

    onFire(callback) {
        this.callbacks.fire = callback
    }

    onRequest(callback) {
        this.callbacks.request = callback
    }

    onBeforeResponse(callback) {
        this.callbacks.beforeResponse = callback
    }

    onResponse(callback) {
        this.callbacks.response = callback
    }

    onSuccess(callback) {
        this.callbacks.success = callback
    }

    onError(callback) {
        this.callbacks.error = callback
    }

    onCancel(callback) {
        this.callbacks.cancel = callback
    }

    onBeforeMorph(callback) {
        this.callbacks.beforeMorph = callback
    }

    onAfterMorph(callback) {
        this.callbacks.afterMorph = callback
    }

    fire(el, directive, component) {
        this.callbacks.default({el, directive, component, request: this})

        this.callbacks.fire()
    }

    request() {
        this.callbacks.request()
    }

    beforeResponse() {
        this.callbacks.beforeResponse()
    }

    response() {
        this.callbacks.response()
    }

    success() {
        this.callbacks.success()
    }

    error() {
        this.callbacks.error()
    }

    cancel() {
        this.callbacks.cancel()
    }

    beforeMorph() {
        this.callbacks.beforeMorph()
    }

    afterMorph() {
        this.callbacks.afterMorph()
    }
}

export default Interceptor