import http from './connection/http'
import websocket from './connection/websocket'
import Connection from './connection'
import ComponentManager from './component_manager'

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
