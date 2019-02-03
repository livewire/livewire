import Root from "./Root";
const prefix = require('./prefix.js')()

export default class {
    constructor(backend) {
        this.backend = backend

        const els = document.querySelectorAll(`[${prefix}\\:root]`)

        this.roots = {}

        Array.from(els).forEach(el => {
            this.roots[el.getAttribute(`${prefix}:root`)] = new Root(el)

            if (el.closest(`[${prefix}\\:root]`)) {
                this.roots[el.getAttribute(`${prefix}:root`)].setParent(el.closest(`[${prefix}\\:root]`))
            }
        })
    }

    add(el) {
        this.roots[el.getAttribute(`${prefix}:root`)] = new Root(el)
        this.backend.message({
            event: 'init',
            payload: {},
            component: el.getAttribute(`${prefix}:root`),
        })
    }

    isRoot(el) {
        return el.hasAttribute(`${prefix}:root`)
    }

    init() {
        Object.keys(this.roots).forEach(key => {
            this.backend.message({
                event: 'init',
                payload: {},
                component: key,
            })
        })
    }

    find(componentName) {
        return this.roots[componentName]
    }

    get count() {
        return Object.keys(this.roots).length
    }
}
