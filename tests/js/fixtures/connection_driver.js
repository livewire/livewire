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
        if (this.config.response || this.config.error) {
            setTimeout(() => {
                if (this.config.error) {
                    this.onError !== null && this.onError({ id: payload.id })
                } else {
                    this.onMessage && this.onMessage({
                        id: payload && payload.id,
                        fromPrefetch: payload && payload.fromPrefetch,
                        ...this.config.response,
                    })
                }
            }, this.config.delay || 1)
        }
    },
}
