const prefix = require('./prefix.js')()
const morphdom = require('morphdom');
import roots from './roots.js'
import initializeNode from './nodeInitializer.js'

export default function (component, dom, dirtyInputs) {
    morphdom(roots.find(component).el.firstElementChild, dom, {
        onBeforeNodeAdded(node) {
            if (typeof node.hasAttribute !== 'function') {
                return
            }
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

        onBeforeNodeDiscarded(node) {
            if (typeof node.hasAttribute !== 'function') {
                return
            }
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

        onBeforeElChildrenUpdated(from, to) {
            // This allows nesting components
            if (from.hasAttribute(`${prefix}:root`)) {
                return false
            }
        },

        onBeforeElUpdated(el) {
            // This will need work. But is essentially "input persistance"
            const isInput = (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')

            if (isInput) {
                if (el.type === 'submit') {
                    return true
                }

                const isSync = el.hasAttribute(`${prefix}:sync`)

                if (isSync) {
                    const syncName = el.getAttribute(`${prefix}:sync`)
                    if (Array.from(dirtyInputs).includes(syncName)) {
                        return true
                    } {
                        return false
                    }
                }

                return false
            }
        },

        onNodeAdded(node) {
            if (typeof node.hasAttribute !== 'function') {
                return
            }

            if (roots.isRoot(node)) {
                roots.add(node)
            } else {
                initializeNode(node)
            }
        },
    });
}
