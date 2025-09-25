
export class MessageRequest {
    messages = new Set()
    controller = new AbortController()
    payload = null
    respondCallbacks = []
    succeedCallbacks = []
    failCallbacks = []

    initInterceptors(interceptorRegistry) {
        this.messages.forEach(message => {
            let interceptors = interceptorRegistry.getRelevantInterceptors(message)

            message.setInterceptors(interceptors)
        })
    }

    addMessage(message) {
        this.messages.add(message)
    }

    cancel() {
        this.controller.abort('cancelled')

        this.messages.forEach(message => message.cancel())
    }

    isCancelled() {
        if (this.controller.signal.aborted) return true

        return Array.from(this.messages).every(message => message.isCancelled())
    }

    /**
     * Lifecycle methods
     */
    onSend() {
        this.messages.forEach(message => message.onSend())
    }

    onError(status, responseContent, preventDefault) {
        this.messages.forEach(message => message.onError(status, responseContent, preventDefault))
    }

    onSuccess() {
        this.messages.forEach(message => message.onSuccess())
    }

    /**
     * End of lifecycle methods
     */

    respond(status, response) {
        this.messages.forEach(message => message.respond())

        this.respondCallbacks.forEach(i => i({ status, response }))
    }

    fail(status, content, preventDefault) {
        this.messages.forEach(message => message.fail())

        this.failCallbacks.forEach(i => i({ status, content, preventDefault }))
    }

    succeed(status, json) {
        this.messages.forEach(message => message.succeed())

        this.succeedCallbacks.forEach(i => i({ status, json }))
    }
}

export class PageRequest {
    controller = new AbortController()

    constructor(uri) {
        this.uri = uri
    }

    cancel() {
        this.controller.abort('cancelled')
    }

    isCancelled() {
        return this.controller.signal.aborted
    }
}
