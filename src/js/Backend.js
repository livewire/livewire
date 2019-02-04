export default class {
    constructor(connection) {
        this.connection = connection
    }

    init(config) {
        this.connection.connect({
            onOpen: config.onConnect,
            onMessage: config.onMessageReceived,
        })
    }

    message(payload) {
        this.connection.sendMessage(payload)
    }
}
