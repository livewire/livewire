import Root from "./Root";
import connection from './connection.js'
const prefix = require('./prefix.js')()

export default {
    roots: {},

    init() {
        const els = document.querySelectorAll(`[${prefix}\\:root]`)

        Array.from(els).forEach(el => {
            this.roots[el.getAttribute(`${prefix}:root`)] = new Root(el)

            if (el.closest(`[${prefix}\\:root]`)) {
                this.roots[el.getAttribute(`${prefix}:root`)].setParent(el.closest(`[${prefix}\\:root]`))
            }
        })

        this.sendMessage()
    },

    add(el) {
        this.roots[el.getAttribute(`${prefix}:root`)] = new Root(el)
        connection.sendMessage({
            event: 'init',
            payload: {},
            component: el.getAttribute(`${prefix}:root`),
        })
    },

    isRoot(el) {
        return el.hasAttribute(`${prefix}:root`)
    },

    sendMessage() {
        Object.keys(this.roots).forEach(key => {
            connection.sendMessage({
                event: 'init',
                payload: {},
                component: key,
            })
        })
    },

    getRootNameFromEl(el) {
        return el.closest(`[${prefix}\\:root]`).getAttribute(`${prefix}:root`)
    },

    find(componentName) {
        return this.roots[componentName]
    },

    get count() {
        return Object.keys(this.roots).length
    },
}
