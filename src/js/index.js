import ComponentManager from './ComponentManager'
import http from './HttpConnection'
import websockets from './WebSocket'
import NodeInitializer from './NodeInitializer'
import Connection from './Connection'

const Livewire = {
    start(options) {
        if (! options) {
            options = {};
        }

        const driver = options.driver || 'http'

        if (driver === 'websockets') {
            var driverInstance = websockets
        } else {
            var driverInstance = http
        }

        const nodeInitializer = new NodeInitializer(new Connection(driverInstance))

        this.components = new ComponentManager(nodeInitializer)

        this.components.init()
    },

    stop() {
        if (this.components) {
            this.components.destroy()
        }
    }
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

export default Livewire
