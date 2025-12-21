
export class MessageRequest {
    messages = new Set()
    controller = new AbortController()
    interceptors = []
    cancelled = false
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

    cancel() {
        if (this.cancelled) return

        this.cancelled = true

        this.controller.abort()

        this.messages.forEach(message => {
            if (message.isCancelled()) return

            message.cancel()
        })
    }

    hasAllCancelledMessages() {
        return this.getActiveMessages().size === 0
    }

    isCancelled() {
        return this.cancelled
    }

    isIsolated() {
        return Array.from(this.messages).every(message => message.component.isIsolated)
    }

    /**
     * Lifecycle methods
     */
    invokeOnSend({ responsePromise }) {
        this.interceptors.forEach(interceptor => interceptor.onSend({ responsePromise }))

        this.messages.forEach(message => message.invokeOnSend())
    }

    invokeOnCancel() {
        this.interceptors.forEach(interceptor => interceptor.onCancel())
    }

    invokeOnFailure({ error }) {
        this.interceptors.forEach(interceptor => interceptor.onFailure({ error }))

        this.messages.forEach(message => message.invokeOnFailure(error))
    }

    invokeOnResponse({ response }) {
        this.interceptors.forEach(interceptor => interceptor.onResponse({ response }))
    }

    invokeOnStream({ response }) {
        this.interceptors.forEach(interceptor => interceptor.onStream({ response }))
    }

    invokeOnParsed({ response, body }) {
        this.interceptors.forEach(interceptor => interceptor.onParsed({ response, body }))
    }


    invokeOnRedirect({ url, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onRedirect({ url, preventDefault }))
    }

    invokeOnDump({ html, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onDump({ html, preventDefault }))
    }

    invokeOnError({ response, body, preventDefault }) {
        this.interceptors.forEach(interceptor => interceptor.onError({ response, body, preventDefault }))

        this.messages.forEach(message => message.invokeOnError({ response, body, preventDefault }))
    }

    invokeOnSuccess({ response, body, json }) {
        this.interceptors.forEach(interceptor => interceptor.onSuccess({ response, body, json }))
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
