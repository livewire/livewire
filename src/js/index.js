import ComponentManager from './ComponentManager'
import http from './httpConnection'
import websockets from './webSocket'
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

        const roots = new ComponentManager(nodeInitializer)

        roots.init()
    }
}

if (!window.Livewire) {
    window.Livewire = livewire
}

export default livewire
