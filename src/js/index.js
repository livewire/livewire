import http from './HttpConnection'
import websocket from './WebSocket'
import Connection from './Connection'
import ComponentManager from './ComponentManager'

const Livewire = {
    start(options) {
        if (! options) {
            options = {};
        }

        const driverInstance = options.driver === 'websocket'
            ? websocket
            : http

        const connection = new Connection(driverInstance)

        this.components = new ComponentManager(connection)

        this.components.mount()
    },

    stop() {
        this.components && this.components.destroy()
    }
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

export default Livewire
