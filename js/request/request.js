
export class MessageRequest {
    messages = new Set()
    controller = new AbortController()
    interceptors = []
    cancelled = false
    uri = null
    payload = null
    options = null

    addMessage(message) {
        this.messages.add(message)
    }

    getActiveMessages() {
        return new Set([...this.messages].filter(message => ! message.isCancelled()))
    }

    initInterceptors(interceptorRegistry) {
        this.interceptors = interceptorRegistry.getRequestInterceptors(this)

        this.messages.forEach(message => {
            let messageInterceptors = interceptorRegistry.getMessageInterceptors(message)

            message.setInterceptors(messageInterceptors)
        })

        this.interceptors.forEach(interceptor => interceptor.init())

        this.messages.forEach(message => {
            message.getInterceptors().forEach(interceptor => interceptor.init())
        })
    }

    cancel() {
        if (this.cancelled) return

        this.cancelled = true

        this.controller.abort('cancelled')

        this.messages.forEach(message => message.cancel())
    }

    hasAllCancelledMessages() {
        return this.getActiveMessages().size === 0
    }

    isCancelled() {
        return this.cancelled
    }

    /**
     * Lifecycle methods
     */
    onSend({ responsePromise }) {
        this.interceptors.forEach(interceptor => interceptor.onSend({ responsePromise }))

        this.messages.forEach(message => message.onSend())
    }

    onFailure({ error }) {
        this.interceptors.forEach(interceptor => interceptor.onFailure({ error }))
    }

    onResponse({ response }) {
        this.interceptors.forEach(interceptor => interceptor.onResponse({ response }))
    }

    onParsed({ response, responseBody }) {
        this.interceptors.forEach(interceptor => interceptor.onParsed({ response, responseBody }))
    }

    onRedirect({ url, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onRedirect({ url, preventDefault }))
    }

    onDump({ content, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onDump({ content, preventDefault }))
    }

    onError({ response, responseBody, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onError({ response, responseBody, preventDefault }))

        this.messages.forEach(message => message.onError({ response, responseBody, preventDefault }))
    }

    onSuccess({ response, responseBody, responseJson }) {
        this.interceptors.forEach(interceptor => interceptor.onSuccess({ response, responseBody, responseJson }))
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
