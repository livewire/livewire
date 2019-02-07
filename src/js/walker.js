// A little DOM walker I made, because document.TreeWalker isn't good at
// conditionally not-traversing down a node.

export default {
    root: null,
    callback: null,
    depth: 0,

    walk(root, callback) {
        this.root = root
        this.callback = callback
        return this.start(root)
    },

    start(node) {
        if (this.callback(node) === false) {
            return this.goToNextSiblingOrUpToParent(node)
        }

        let child = this.getFirstChild(node)
        if (child) {
            this.depth++
            return this.start(child)
        } else {
            return this.goToNextSiblingOrUpToParent(node)
        }
    },

    goToNextSiblingOrUpToParent(node) {
        let sibling = this.getNextSibling(node)
        if (sibling) {
            return this.start(sibling)
        } else {
            if (this.depth < 0) {
                return
            } else {
                this.depth--
                return this.goToNextSiblingOrUpToParent(node.parentNode)
            }
        }
    },

    getFirstChild(node) {
        const child = node.firstChild

        if (child && child.nodeType !== Node.ELEMENT_NODE) {
            return this.getNextSibling(child)
        }
        return child
    },

    getNextSibling(node) {
        if (node === null) debugger
        const sibling = node.nextSibling

        if (sibling && sibling.nodeType !== Node.ELEMENT_NODE) {
            return this.getNextSibling(sibling)
        }
        return sibling
    },
}
