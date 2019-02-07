const prefix = require('./prefix.js')()
const morphdom = require('morphdom');
import walker from './walker.js'
import initializeNode from './nodeInitializer.js'
import roots from './roots.js';
import handleMorph from './handleMorph.js';

export default class Root {
    constructor(id, el, forceUpdate) {
        this.children = {}
        this.id = id
        console.log(id)
        this.serialized = window.Livewire.components[this.id].serialized

        if (forceUpdate) {
            morphdom(el, window.Livewire.components[this.id].dom)
            // handleMorph(this.el, window.Livewire.components[this.id].dom, [])
        }
        this.inititializeNodes()
    }

    get el() {
        return document.querySelector(`[${prefix}\\:root-id="${this.id}"]`)
    }

    inititializeNodes() {
        walker.walk(this.el, node => {
            if (this.el.isSameNode(node)) {
                return
            }

            if (roots.isRoot(node)) {
                const id = node.getAttribute(`${prefix}:root-id`)
                const root = new Root(id, node)
                this.children[root.id] = root
                roots.add(root.id, root)
                return false
            }

            initializeNode(node);
        })
    }

    replace(dom, dirtyInputs) {
        handleMorph(this.el, dom, dirtyInputs)
    }
}
