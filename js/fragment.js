
export function closestFragment(el, { isMatch, hasReachedBoundary }) {
    if (! hasReachedBoundary) hasReachedBoundary = () => false

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
                    let metadata = extractFragmentMetadataFromMarkerNode(sibling)

                    if (isMatch(metadata)) {
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
    if (! hasReachedBoundary) hasReachedBoundary = () => false

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
                let metadata = extractFragmentMetadataFromMarkerNode(node)

                if (isMatch(metadata)) {
                    startNode = node

                    stop()
                }
            }
        })
    })

    return startNode && new Fragment(startNode)
}

export function isStartFragmentMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if FRAGMENT')
}

export function isEndFragmentMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if ENDFRAGMENT')
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

export class Fragment {
    constructor(startMarkerNode) {
        this.startMarkerNode = startMarkerNode

        this.metadata = extractFragmentMetadataFromMarkerNode(startMarkerNode)
    }

    get endMarkerNode() {
        return findMatchingEndMarkerNode(this.startMarkerNode, this.metadata)
    }

    append(mountContainerTagName, html) {
        let container = document.createElement(mountContainerTagName)

        container.innerHTML = html

        Array.from(container.childNodes).forEach(node => {
            this.endMarkerNode.before(node)
        })
    }

    prepend(mountContainerTagName, html) {
        let container = document.createElement(mountContainerTagName)

        container.innerHTML = html

        Array.from(container.childNodes)
            .reverse()
            .forEach(node => {
                this.startMarkerNode.after(node)
            })
    }
}

export function findMatchingEndMarkerNode(startMarkerNode, metadata) {
    let current = startMarkerNode

    while (current) {
        if (isEndFragmentMarker(current)) {
            let currentMetadata = extractFragmentMetadataFromMarkerNode(current)

            if (Object.keys(metadata).every(key => metadata[key] === currentMetadata[key])) {
                return current
            }
        }

        current = current.nextSibling
    }

    return null
}

export function extractInnerHtmlFromFragmentHtml(fragmentHtml) {
    let regex = /<!--\[if FRAGMENT\b.*?\]><!\[endif\]-->([\s\S]*)<!--\[if ENDFRAGMENT\b.*?\]><!\[endif\]-->/i;

    let match = fragmentHtml.match(regex)

    if (! match) throw new Error('Invalid fragment marker')

    let [_, html] = match

    return html
}

export function extractFragmentMetadataFromHtml(fragmentHtml) {
    let regex = /\[if (FRAGMENT|ENDFRAGMENT):(.*?)\]/

    let match = fragmentHtml.match(regex)

    if (! match) throw new Error('Invalid fragment marker')

    let [_, __, encodedMetadata] = match

    return decodeMetadata(encodedMetadata)
}

export function extractFragmentMetadataFromMarkerNode(startMarkerNode) {
    let regex = /\[if (FRAGMENT|ENDFRAGMENT):(.*?)\]/

    let match = startMarkerNode.textContent.match(regex)

    if (! match) throw new Error('Invalid fragment marker')

    let [_, __, encodedMetadata] = match

    return decodeMetadata(encodedMetadata)
}

export function decodeMetadata(encodedMetadata) {
    let metadata = {}

    // Split by pipe character to get key=value pairs
    let pairs = encodedMetadata.split('|')

    pairs.forEach(pair => {
        // Split each pair by = to get key and value
        let [key, value] = pair.split('=')
        metadata[key] = value
    })

    return metadata
}
