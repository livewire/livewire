export default class {
    connect() {
        this.wsConnection = new WebSocket('ws://localhost:8080');
    }

    sendMessage(payload) {
        this.wsConnection.send(JSON.stringify(payload))
    }

    onOpen(callback) {
        this.wsConnection.onopen = callback
    }

    onMessage(callback) {
        this.wsConnection.onmessage = e => {
            callback(JSON.parse(e.data))
        }
    }
}
