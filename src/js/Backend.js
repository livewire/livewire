export default class {
    constructor(connection) {
        this.connection = connection
    }

    init(config) {
        this.connection.connect()
        this.connection.onOpen(config.onConnect)
        this.connection.onMessage(config.onMessageReceived)
    }

    message(payload) {
        this.connection.sendMessage(payload)
    }
}
