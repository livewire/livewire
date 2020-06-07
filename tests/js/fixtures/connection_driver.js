export default {
    onError: null,
    onMessage: null,
    config: {},

    init() {
        //
    },

    sendMessage(payload) {
        if (this.config.requestInterceptor) {
            this.config.requestInterceptor(payload)
        }
        if (this.config.response) {
            let response = this.fetchResponse()
            setTimeout(() => {
                if (response.error) {
                    delete response.error
                    this.onError !== null && this.onError({ id: payload.id, ...response })
                } else {
                    this.onMessage && this.onMessage({
                        id: payload && payload.id,
                        fromPrefetch: payload && payload.fromPrefetch,
                        ...response
                    })
                }
            }, this.config.delay || 1)
        }
    },

    fetchResponse() {
        if (this.config.response && Array.isArray(this.config.response)) {
            return this.config.response.shift() || {}
        }

        return this.config.response || {}
    },
}
