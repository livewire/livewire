export default class {
    connect(config) {
        this.wsConnection = new WebSocket('ws://localhost:8080');
        this.wsConnection.onopen = config.onOpen
        this.wsConnection.onmessage = e => {
            config.onMessage(JSON.parse(e.data))
        }
        this.wsConnection.onclose = () => {
            console.log('Connection closed')
            setTimeout(() => {this.connect(config)}, 1000)
        }
    }

    sendMessage(payload) {
        this.wsConnection.send(JSON.stringify(payload))
    }
}
