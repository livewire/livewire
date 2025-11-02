
export class MessageRequest {
    messages = new Set()
    controller = new AbortController()
    interceptors = []
    aborted = false
    uri = null
    payload = null
    options = null

    addMessage(message) {
        message.setRequest(this)

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

    abort() {
        if (this.aborted) return

        this.aborted = true

        this.controller.abort()

        this.messages.forEach(message => {
            if (message.isCancelled()) return

            message.cancel()
        })
    }

    hasAllCancelledMessages() {
        return this.getActiveMessages().size === 0
    }

    isAborted() {
        return this.aborted
    }

    isAsync() {
        return [...this.messages].every(message => message.isAsync() || message.isIsolated())
    }

    /**
     * Lifecycle methods
     */
    onSend({ responsePromise }) {
        this.interceptors.forEach(interceptor => interceptor.onSend({ responsePromise }))

        this.messages.forEach(message => message.onSend())
    }

    onAbort() {
        this.interceptors.forEach(interceptor => interceptor.onAbort())
    }

    onFailure({ error }) {
        this.interceptors.forEach(interceptor => interceptor.onFailure({ error }))
    }

    onResponse({ response }) {
        this.interceptors.forEach(interceptor => interceptor.onResponse({ response }))
    }

    onStream({ response }) {
        this.interceptors.forEach(interceptor => interceptor.onStream({ response }))
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
        this.controller.abort()
    }

    isCancelled() {
        return this.controller.signal.aborted
    }
}
