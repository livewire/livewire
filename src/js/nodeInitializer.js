import renameme from './renameme'
import connection from './connection.js'
import roots from './roots.js'
const prefix = require('./prefix.js')()

export default function (node) {
    if (node.hasAttribute(`${prefix}:click`)) {
        renameme.attachClick(node, (method, params, el) => {
            connection.sendMethod(method, params, roots.getRootNameFromEl(el))
        })
    }

    if (node.hasAttribute(`${prefix}:submit`)) {
        renameme.attachSubmit(node, (method, params, el) => {
            sendMethod(method, [params], roots.getRootNameFromEl(el))
        })
    }

    if (node.hasAttribute(`${prefix}:keydown.enter`)) {
        renameme.attachEnter(node, (method, params, el) => {
            connection.sendMethod(method, params, roots.getRootNameFromEl(el))
        })
    }

    if (node.hasAttribute(`${prefix}:sync`)) {
        renameme.attachSync(node, (model, el) => {
            connection.sendSync(model, roots.getRootNameFromEl(el))
        })
    }
}
