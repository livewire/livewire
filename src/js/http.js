import Axios from "axios";

export default {
    init(onMessage) {
        this.onMessageCallback = onMessage
        this.serializedComponents = {}

        return Promise.resolve(this)
    },

    rejectOnClose() {
        return Promise.resolve()
    },

    sendMessage(payload) {
        const thing = this.serializedComponents[payload.component] || null
        Axios.post('/fake-websockets/message', {...payload, ...{ serialized: thing}})
            .then(response => {
                this.onMessageCallback(response.data)
                this.serializedComponents[payload.component] = response.data.serialized
            })
    },

    onMessage(callback) {
        this.onMessageCallback = callback
    }
}
