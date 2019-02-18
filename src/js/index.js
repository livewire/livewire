import ComponentManager from './ComponentManager.js'
import http from './http.js'
import NodeInitializer from './NodeInitializer.js'
import Connection from './Connection.js'

const nodeInitializer = new NodeInitializer(new Connection(http))

const roots = new ComponentManager(nodeInitializer)

export default roots
