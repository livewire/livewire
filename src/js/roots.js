import Root from "./Root";
const prefix = require('./prefix.js')()

export default {
    roots: {},
    allRoots: {},

    init() {
        const el = document.querySelector(`[${prefix}\\:root-id]`)

        const componentId = el.getAttribute(`${prefix}:root-id`)
        const root = new Root(componentId, el, true)
        this.roots[root.id] = root
        this.allRoots[root.id] = root
        window.roots = this
    },

    add(id, root) {
        this.allRoots[id] = root
    },

    isRoot(el) {
        return (typeof el.hasAttribute === 'function') && el.hasAttribute(`${prefix}:root-id`)
    },

    getRootIdFromEl(el) {
        return el.closest(`[${prefix}\\:root-id]`).getAttribute(`${prefix}:root-id`)
    },

    find(id) {
        return this.allRoots[id]
    },

    findByEl(el) {
        return this.find(this.getRootIdFromEl(el))
    },

    get count() {
        return Object.keys(this.roots).length
    },
}
