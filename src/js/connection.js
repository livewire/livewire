import webSocket from './webSocket.js'
import http from './http.js'
import roots from './roots.js';

export default {
    connection: null,

    init: async function () {
        try {
            this.connection = await webSocket.init()
            console.log('using websockets')
        } catch (error) {
            this.connection = http.init()
            console.log('using http')
        }

        this.connection.fallback = () => {
            console.log('switching back to http')
            this.connection = http.init()
            this.connection.onMessage = (payload) => {
                this.onMessage(payload)
            }
        }

        this.connection.onMessage = (payload) => {
            this.onMessage(payload)
        }

        // I'm sorry for these terrible things.
        this.connection.refreshDom = this.refreshDom.bind(this)

        this.connection.wireUp()

        return this.connection
    },

    onMessage(payload) {
        roots.find(payload.id).replace(payload.dom, payload.dirtyInputs)
        roots.find(payload.id).serialized = payload.serialized
    },

    sendMessage(data, root) {
        this.connection.sendMessage({
            ...data,
            ...{ serialized: root.serialized },
        });
    },

    sendMethod(method, params, root) {
        this.sendMessage({
            event: 'fireMethod',
            data: {
                method,
                params,
            },
        }, root)
    },

    sendSync(name, value, root) {
        this.sendMessage({
            event: 'syncInput',
            data: { name, value },
        }, root)
    },

    refreshDom() {
        Object.keys(roots.allRoots).forEach(id => {
            this.sendMessage({event: 'refresh', data: {}}, roots.allRoots[id])
        })
    },
}
