import Root from "./Root";

export default class {
    constructor(backend) {
        this.backend = backend

        const els = document.querySelectorAll('[livewire\\:root]')

        this.roots = {}

        Array.from(els).forEach(el => {
            this.roots[el.getAttribute('livewire:root')] = new Root(el)

            if (el.closest('[livewire\\:root]')) {
                this.roots[el.getAttribute('livewire:root')].setParent(el.closest('[livewire\\:root]'))
            }
        })
    }

    add(el) {
        this.roots[el.getAttribute('livewire:root')] = new Root(el)
        this.backend.message({
            event: 'init',
            payload: {},
            component: el.getAttribute('livewire:root'),
        })
    }

    isRoot(el) {
        return el.hasAttribute('livewire:root')
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
