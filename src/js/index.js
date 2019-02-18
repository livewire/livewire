import ComponentManager from './ComponentManager.js'
import http from './http.js'
import websockets from './webSocket'
import NodeInitializer from './NodeInitializer.js'
import Connection from './Connection.js'

export default {
    init(options) {
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
