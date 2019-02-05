import webSocket from './webSocket.js'
import http from './http.js'

/**
 * I'm so sorry for how rediculously hard to follow this all is.
 * I'm just so sorry...
 */
export default {
    connection: null,
    onMessageCallback: null,

    init() {
        const onMessage = (payload) => {
            this.onMessageCallback(payload)
        }

        const fallback = () => {
            console.log('use http instead of websockets')
            http.init(onMessage).then(connection => {
                this.connection = connection
            })
        }

        return new Promise(resolve => {
            webSocket.init(onMessage, fallback)
                .catch(() => {
                    console.log('websockets didnt work')
                    return http.init(onMessage)
                })
                .then(connection => {
                    console.log('use websockets or http')
                    this.connection = connection
                    resolve()
                })
        })
    },

    onMessage(callback) {
        this.onMessageCallback = callback
    },

    sendMethod(method, params, component) {
        this.sendMessage({
            event: 'fireMethod',
            payload: {
                method,
                params,
            },
            component
        })
    },

    sendSync(model, value, component) {
        this.sendMessage({
            event: 'sync',
            payload: { model, value },
            component,
        })
    },

    sendMessage(payload) {
        this.connection.sendMessage(payload);
    },

}
