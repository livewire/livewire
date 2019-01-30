import Axios from "axios";

export default class {
    connect() {
        this.serializedComponents = {}
    }

    sendMessage(payload) {
        const thing = this.serializedComponents[payload.component] || null
        Axios.post('/fake-websockets/message', {...payload, ...{ serialized: thing}})
            .then(response => {
                this.onMessageCallback(response.data)
                this.serializedComponents[payload.component] = response.data.serialized
            })
    }

    onOpen(callback) {
        callback()
        // this.wsConnection.onopen = callback
    }

    onMessage(callback) {
        this.onMessageCallback = callback
    }
}
