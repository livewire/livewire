import Root from "./Root";
const prefix = require('./prefix.js')()

export default {
    roots: {},
    allRoots: {},

    init() {
        this.rootElements().forEach(el => {
            const componentId = el.getAttribute(`${prefix}:root-id`)
            const root = new Root(componentId, el, true)
            this.roots[root.id] = root
            this.allRoots[root.id] = root
        })
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

    rootElements() {
        // In CSS, it's simple to select all elements that DO have a certain ancestor.
        // However, it's not simple (kinda impossible) to select elements that DONT have
        // a certain ancestor. Therefore, we will flip the logic (select all roots that have
        // have a root ancestor), then select all roots, then take a diff of the two.

        // Convert NodeLists to Arrays. Ew.
        const allEls = Array.prototype.slice.call(
            document.querySelectorAll(`[${prefix}\\:root-id]`)
        )
        const onlyChildEls = Array.prototype.slice.call(
            document.querySelectorAll(`[${prefix}\\:root-id] [${prefix}\\:root-id]`)
        )

        return allEls.filter(el => {
            return ! onlyChildEls.includes(el)
        })
    },
}
