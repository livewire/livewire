export default class {
    constructor(connection, fallback = null) {
        this.connection = connection
        this.fallback = fallback
    }

    init(config) {
        this.connection.connect({
            onOpen: config.onConnect,
            onMessage: config.onMessageReceived,
        }).catch(() => {
            this.fallback.connect(config)
        })
    }

    message(payload) {
        this.connection.sendMessage(payload)
    }
}
