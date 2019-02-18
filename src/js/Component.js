import walker from './walker.js'
import { hasAttribute, getAttribute, elByAttributeAndValue, elsByAttributeAndValue, preserveActiveElement } from './domHelpers'
const prefix = require('./prefix.js')()
import morphdom from './morphdom/index.js'
import rootsStore from './rootsStore'

export default class Component {
    constructor(el, nodeInitializer, parent, dontInitialize) {
        this.children = {}
        this.parent = parent
        this.nodeInitializer = nodeInitializer
        this.id = getAttribute(el, 'root-id')
        this.serialized = getAttribute(el, 'root-serialized')

        if (! dontInitialize) {
            this.inititializeNodes()
        }
    }

    get el() {
        return elByAttributeAndValue('root-id', this.id)
    }

    inititializeNodes() {
        walker.walk(this.el, (node) => {
            if (this.el.isSameNode(node)) {
                return
            }

            if (this.isRoot(node)) {
                this.addChildRoot(node)
                return false
            }

            this.nodeInitializer.initialize(node);
        })
    }

    addChildRoot(node, dontInitialize) {
        const component = new Component(node, this.nodeInitializer, this, dontInitialize)
        this.children[component.id] = component
        rootsStore[component.id] = component
    }

    replace(dom, dirtyInputs, serialized) {
        this.serialized = serialized;

        // Prevent morphdom from moving an input element and it losing it's focus.
        preserveActiveElement(() => {
            this.handleMorph(dom, dirtyInputs)
        })
    }

    setLoading(refName) {
        elsByAttributeAndValue('loading', refName, this.el).forEach(el => {
            el.classList.remove('hidden')
        })
    }

    unsetLoading(refName) {
        elsByAttributeAndValue('loading', refName, this.el).forEach(el => {
            el.classList.add('hidden')
        })
    }

    handleMorph(dom, dirtyInputs) {
        morphdom(this.el, dom, {
            childrenOnly: false,

            getNodeKey: (node) => {
                return node.id;
            },

            onBeforeNodeAdded: (node) => {
                // console.log(node)
                if (typeof node.hasAttribute !== 'function') {
                    return
                }

                // if (node.hasAttribute(`${prefix}:root-id`) && !from.isSameNode(el)) {
                //     console.log('should hit (added)')
                //     return false
                // }
                // console.log('before node added: ', node)
                // console.log(node)
                if (node.hasAttribute(`${prefix}:transition`)) {
                    const transitionName = node.getAttribute(`${prefix}:transition`)

                    node.classList.add(`${transitionName}-enter`)
                    node.classList.add(`${transitionName}-enter-active`)

                    setTimeout(() => {
                        node.classList.remove(`${transitionName}-enter`)
                        setTimeout(() => {
                            node.classList.remove(`${transitionName}-enter-active`)
                        }, 500)
                    }, 65)
                }
            },

            onBeforeNodeDiscarded: (node) => {
                if (typeof node.hasAttribute !== 'function') {
                    return
                }

                // if (node.hasAttribute(`${prefix}:root-id`) && !from.isSameNode(el)) {
                //     console.log('should hit (added)')
                //     return false
                // }
                // console.log('before node discarded: ', node)
                // if (typeof node.hasAttribute !== 'function') {
                //     return
                // }
                if (node.hasAttribute(`${prefix}:transition`)) {
                    const transitionName = node.getAttribute(`${prefix}:transition`)

                    node.classList.add(`${transitionName}-leave-active`)

                    setTimeout(() => {
                    node.classList.add(`${transitionName}-leave-to`)
                        setTimeout(() => {
                            node.classList.remove(`${transitionName}-leave-active`)
                            node.classList.remove(`${transitionName}-leave-to`)
                            node.remove()
                        }, 500)
                    }, 65)

                    return false
                }
            },

            onBeforeElChildrenUpdated: (from, to) => {
                if (from.hasAttribute(`${prefix}:root-id`) && !from.isSameNode(this.el)) {
                    return false
                }
            },

            onBeforeElUpdated: (from, to) => {
                if (from.hasAttribute(`${prefix}:root-id`) && !from.isSameNode(this.el)) {
                    return false
                }
                // console.log('before from updated: ', from, to)
                // This will need work. But is essentially "input persistance"
                const isInput = (from.tagName === 'INPUT' || from.tagName === 'TEXTAREA')

                if (isInput) {
                    if (from.type === 'submit') {
                        return true
                    }

                    const isSync = from.hasAttribute(`${prefix}:sync`)

                    if (isSync) {
                        const syncName = from.getAttribute(`${prefix}:sync`)
                        if (Array.from(dirtyInputs).includes(syncName)) {
                            return true
                        } {
                            return false
                        }
                    }

                    return false
                }
            },

            onNodeAdded: (node) => {
                if (typeof node.hasAttribute !== 'function') {
                    return
                }

                if (this.isRoot(node)) {
                    // The "true" means don't initialize because this will
                    this.addChildRoot(node, true)
                }

                this.nodeInitializer.initialize(node)
            },
        });
    }

    isRoot(el) {
        return (typeof el.hasAttribute === 'function') && hasAttribute(el, 'root-id')
    }
}
