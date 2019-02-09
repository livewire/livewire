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
        roots.find(payload.component).replace(payload.dom, payload.dirtyInputs)
        roots.find(payload.component).serialized = payload.serialized
    },

    sendMessage(payload, root) {
        this.connection.sendMessage({
            ...payload,
            ...{
                component: root.id,
                serialized: root.serialized,
            },
        });
    },

    sendMethod(method, params, root) {
        this.sendMessage({
            event: 'fireMethod',
            payload: {
                method,
                params,
            },
        }, root)
    },

    sendSync(model, value, root) {
        this.sendMessage({
            event: 'sync',
            payload: { model, value },
        }, root)
    },

    refreshDom() {
        Object.keys(roots.allRoots).forEach(id => {
            this.sendMessage({event: 'refresh', payload: {}}, roots.allRoots[id])
        })
    },
}
