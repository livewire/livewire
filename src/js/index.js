import ComponentManager from './ComponentManager'
import http from './HttpConnection'
import websockets from './WebSocket'
import NodeInitializer from './NodeInitializer'
import Connection from './Connection'

const livewire = {
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

        this.roots = new ComponentManager(nodeInitializer)

        this.roots.init()
    },

    stop() {
        if (this.roots) {
            this.roots.destroy()
        }
    }
}

if (!window.Livewire) {
    window.Livewire = livewire
}

export default livewire
