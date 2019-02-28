export default {
    onMessage: null,

    init() {
        this.ws = new WebSocket(`ws://${window.location.hostname}:6001`);

        this.ws.onopen = () => {
            console.log('Successfully connected to websocket server')
        }

        this.ws.onerror = e => {
            throw new Error('Can\'t connect to websocket server')
        }

        this.ws.onclose = () => {
            throw new Error('Lost websocket connection')
        }

        this.ws.onmessage = e => {
            this.onMessage.call(this, JSON.parse(e.data))
        }
    },

    wireUp() {
        //
    },

    sendMessage(payload) {
        this.ws.send(JSON.stringify(payload))
    }
}
