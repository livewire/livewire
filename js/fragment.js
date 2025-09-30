
export function isStartFragmentMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if FRAGMENT')
}

export function isEndFragmentMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if ENDFRAGMENT')
}

export function closestFragment(el, { isMatch, hasReachedBoundary }) {
    let current = el

    while (current) {
        // Check previous siblings
        let sibling = current.previousSibling;

        let foundEndMarker = []

        while (sibling) {
            if (isEndFragmentMarker(sibling)) {
                // Keep iterating up until we find the start marker and skip it...
                foundEndMarker.push('a')
            }

            if (isStartFragmentMarker(sibling)) {
                if (foundEndMarker.length > 0) {
                    foundEndMarker.pop()
                } else {
                    let { type, name, mode } = extractFragmentMetadataFromStartMarkerNode(sibling)

                    if (isMatch({ type, name, mode })) {
                        return new Fragment(sibling)
                    }
                }
            }

            sibling = sibling.previousSibling;
        }



        // No start marker found at this level or found end marker
        // Go up to parent unless we've hit the component root
        current = current.parentElement;

        if (current && hasReachedBoundary({ el: current })) {
            break; // Stop at component root
        }
    }

    return null;
}

export function findFragment(el, { isMatch, hasReachedBoundary }) {
    let startNode = null

    let rootEl = el

    walkElements(rootEl, (el, { skip, stop }) => {
        // Skip nested Livewire components
        if (el.hasAttribute && el !== rootEl && hasReachedBoundary({ el })) {
            return skip()
        }

        // Check all child nodes (including text and comment nodes)
        Array.from(el.childNodes).forEach(node => {
            if (isStartFragmentMarker(node)) {
                let { type, name, mode } = extractFragmentMetadataFromStartMarkerNode(node)

                if (isMatch({ type, name, mode })) {
                    startNode = node

                    stop()
                }
            }
        })
    })



    return startNode && new Fragment(startNode)
}

function walkElements(el, callback) {
    let skip = false
    let stop = false

    callback(el, { skip: () => skip = true, stop: () => stop = true })

    if (skip || stop) return

    Array.from(el.children).forEach(child => {
        walkElements(child, callback)

        if (stop) return
    })
}

export class FragmentMetdata {
    constructor(type, name, mode) {
        this.type = type
        this.name = name
        this.mode = mode
    }
}

export class Fragment {
    constructor(startMarkerNode) {
        this.startMarkerNode = startMarkerNode

        this.metadata = extractFragmentMetadataFromStartMarkerNode(startMarkerNode)
    }

    get endMarkerNode() {
        return findMatchingEndMarkerNode(this.startMarkerNode, this.metadata.type, this.metadata.name)
    }
}

export function findMatchingEndMarkerNode(startMarkerNode, type, name) {
    let current = startMarkerNode

    while (current) {
        if (isEndFragmentMarker(current)) {
            let { type: currentType, name: currentName } = extractFragmentMetadataFromEndMarkerNode(current)

            if (currentType === type && currentName === name) {
                return current
            }
        }

        current = current.nextSibling
    }

    return null
}

export function extractInnerHtmlFromFragmentHtml(fragmentHtml) {
    let regex = /<!--\[if FRAGMENT:.*?\]><!\[endif\]-->([\s\S]*?)<!--\[if ENDFRAGMENT:.*?\]><!\[endif\]-->/

    let match = fragmentHtml.match(regex)

    if (! match) throw new Error('Invalid fragment marker')

    let [_, html] = match

    return html
}

export function extractFragmentMetadataFromHtml(fragmentHtml) {
    let regex = /\[if FRAGMENT:([\w-]+):([\w-]+)(?::([\w-]+))?\]/

    let match = fragmentHtml.match(regex)

    if (! match) throw new Error('Invalid fragment marker')

    let [_, type, name, mode] = match

    return { type, name, mode }
}

function extractFragmentMetadataFromStartMarkerNode(startMarkerNode) {
    let regex = /\[if FRAGMENT:([\w-]+):([\w-]+)(?::([\w-]+))?\]/

    let match = startMarkerNode.textContent.match(regex)

    if (! match) throw new Error('Invalid fragment marker')

    let [_, type, name, mode] = match

    return { type, name, mode }
}

function extractFragmentMetadataFromEndMarkerNode(endMarkerNode) {
    let regex = /\[if ENDFRAGMENT:([\w-]+):([\w-]+)(?::([\w-]+))?\]/

    let match = endMarkerNode.textContent.match(regex)

    if (! match) throw new Error('Invalid fragment marker')

    let [_, type, name, mode] = match

    return { type, name, mode }
}
