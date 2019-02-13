const prefix = require('./prefix.js')()
const morphdom = require('morphdom');
import walker from './walker.js'
import initializeNode from './nodeInitializer.js'
import roots from './roots.js';
import handleMorph from './handleMorph.js';

export default class Root {
    constructor(id, el, parent, dontInitialize) {
        this.children = {}
        this.parent = parent
        this.id = id
        console.log(id)
        this.serialized = el.getAttribute(`${prefix}:root-serialized`)

        if (!dontInitialize) {
            this.inititializeNodes()
        }
    }

    get el() {
        return document.querySelector(`[${prefix}\\:root-id="${this.id}"]`)
    }

    inititializeNodes() {
        walker.walk(this.el, (node) => {
            if (this.el.isSameNode(node)) {
                return
            }

            if (roots.isRoot(node)) {
                this.addChildRoot(node)
                return false
            }

            initializeNode(node);
        })
    }

    addChildRoot(node, dontInitialize) {
        const id = node.getAttribute(`${prefix}:root-id`)
        const root = new Root(id, node, this, dontInitialize)
        this.children[root.id] = root
        roots.add(root.id, root)
    }

    replace(dom, dirtyInputs, serialized) {
        this.serialized = serialized;
        handleMorph(this, dom, dirtyInputs)

        console.timeEnd('request')
    }

    setLoading(refName) {
        this.el.querySelectorAll(`[${prefix}\\:loading="${refName}"]`).forEach(el => {
            el.classList.remove('hidden')
        })
    }

    unsetLoading(refName) {
        this.el.querySelectorAll(`[${prefix}\\:loading="${refName}"]`).forEach(el => {
            el.classList.add('hidden')
        })
    }
}
