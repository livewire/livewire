export default {
    // That's a fun param name lol:
    init(onMessage, fallbackCallback) {
        return new Promise((resolve, reject) => {
            this.wsConnection = new WebSocket('ws://localhost:8080');
            this.wsConnection.onerror = e => {
                reject()
            }

            this.wsConnection.onclose = () => {
                fallbackCallback()
            }

            this.wsConnection.onopen = () => {
                resolve(this)
            }

            this.wsConnection.onmessage = e => {
                onMessage(JSON.parse(e.data))
            }
        })
    },

    sendMessage(payload) {
        this.wsConnection.send(JSON.stringify(payload))
    }
}
