const prefix = require('./prefix.js')()
import morphdom from './morphdom'
import roots from './roots.js'
import initializeNode from './nodeInitializer.js'

export default function (el, dom, dirtyInputs) {
    morphdom(el, dom, {
        childrenOnly: false,

        getNodeKey(node) {
            return node.id;
        },

        onBeforeNodeAdded(node) {
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

        onBeforeNodeDiscarded(node) {
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

        onBeforeElChildrenUpdated(from, to) {
            if (from.hasAttribute(`${prefix}:root-id`) && !from.isSameNode(el)) {
                return false
            }
        },

        onBeforeElUpdated(from, to) {
            if (from.hasAttribute(`${prefix}:root-id`) && !from.isSameNode(el)) {
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

        onNodeAdded(node) {
            if (typeof node.hasAttribute !== 'function') {
                return
            }

            initializeNode(node)
        },
    });
}
