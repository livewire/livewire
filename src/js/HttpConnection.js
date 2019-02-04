import Axios from "axios";

export default class {
    connect(config) {
        this.serializedComponents = {}
        this.onMessageCallback = config.onMessage
        config.onOpen()
    }

    sendMessage(payload) {
        const thing = this.serializedComponents[payload.component] || null
        Axios.post('/fake-websockets/message', {...payload, ...{ serialized: thing}})
            .then(response => {
                this.onMessageCallback(response.data)
                this.serializedComponents[payload.component] = response.data.serialized
            })
    }

    onMessage(callback) {
        this.onMessageCallback = callback
    }
}
