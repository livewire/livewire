export default {
    onMessage: null,
    fallback: null,
    refreshDom: null,

    init() {
        this.wsConnection = new WebSocket(`ws://${window.location.hostname}:6001`);

        this.wsConnection.onopen = () => {
            console.log('Successfully connected to websocket server')
        }

        this.wsConnection.onerror = e => {
            throw new Error('Can\'t connect to websocket server')
        }

        this.wsConnection.onclose = () => {
            throw new Error('Lost websocket connection')
        }

        this.wsConnection.onmessage = e => {
            this.onMessage.call(this, JSON.parse(e.data))
        }
    },

    wireUp() {
    },

    sendMessage(payload) {
        this.wsConnection.send(JSON.stringify(payload))
    }
}
