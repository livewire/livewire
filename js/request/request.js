
export class MessageRequest {
    messages = new Set()
    controller = new AbortController()
    respondCallbacks = []
    succeedCallbacks = []
    failCallbacks = []

    cancel() {
        this.controller.abort('cancelled')
    }

    isCancelled() {
        return this.controller.signal.aborted
    }

    addMessage(message) {
        this.messages.add(message)
    }

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
