
// A little DOM-tree walker.
// (TreeWalker won't do because I need to conditionaly ignore sub-trees using the callback)
export function walk(root, callback) {
    if (callback(root) === false) return

    let node = root.firstElementChild

    while (node) {
        walk(node, callback)
        node = node.nextElementSibling
    }
}
