
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

    get contentNodes() {
        let nodes = []
        let current = this.startMarkerNode.nextSibling
        let end = this.endMarkerNode

        while (current && current !== end) {
            nodes.push(current)

            current = current.nextSibling
        }

        return nodes
    }

    append(mountContainerTagName, html) {
        let container = document.createElement(mountContainerTagName)

        container.innerHTML = html

        let incoming = Array.from(container.childNodes)
        let joinable = isOneBlock(this.contentNodes) && isOneBlock(incoming)

        incoming.forEach(node => {
            this.endMarkerNode.before(node)
        })

        if (joinable) joinBlocksAt(meaningfulNodes(incoming)[0])
    }

    prepend(mountContainerTagName, html) {
        let container = document.createElement(mountContainerTagName)

        container.innerHTML = html

        let incoming = Array.from(container.childNodes)
        let existing = this.contentNodes
        let joinable = isOneBlock(existing) && isOneBlock(incoming)
        let existingStart = meaningfulNodes(existing)[0]

        incoming
            .reverse()
            .forEach(node => {
                this.startMarkerNode.after(node)
            })

        if (joinable) joinBlocksAt(existingStart)
    }
}

// An appended or prepended loop brings its own block markers, but a full render draws the loop as
// one block, and morph only pairs keys within matching blocks. Join the two blocks at the seam...
function isStartBlockMarker(node) {
    return node?.nodeType === 8 && node.textContent === '[if BLOCK]><![endif]'
}

function isEndBlockMarker(node) {
    return node?.nodeType === 8 && node.textContent === '[if ENDBLOCK]><![endif]'
}

function isWhitespace(node) {
    return node?.nodeType === 3 && /^[ \t\n\r\f]*$/.test(node.textContent)
}

function meaningfulNodes(nodes) {
    return nodes.filter(node => ! isWhitespace(node))
}

function isOneBlock(nodes) {
    let meaningful = meaningfulNodes(nodes)

    if (! isStartBlockMarker(meaningful[0]) || ! isEndBlockMarker(meaningful[meaningful.length - 1])) return false

    let depth = 0

    for (let i = 0; i < meaningful.length; i++) {
        if (isStartBlockMarker(meaningful[i])) depth++
        if (isEndBlockMarker(meaningful[i])) depth--

        if (depth === 0 && i < meaningful.length - 1) return false
    }

    return true
}

function joinBlocksAt(blockStart) {
    let blockEnd = blockStart.previousSibling

    while (isWhitespace(blockEnd)) blockEnd = blockEnd.previousSibling

    if (! isStartBlockMarker(blockStart) || ! isEndBlockMarker(blockEnd)) return

    let after = blockStart.nextSibling

    blockEnd.remove()
    blockStart.remove()

    // Merge the whitespace left at the seam into one text node, so rows without keys still pair by position...
    let first = after

    while (isWhitespace(first.previousSibling)) first = first.previousSibling

    let run = []

    for (let node = first; isWhitespace(node); node = node.nextSibling) run.push(node)

    if (run.length < 2) return

    run[0].textContent = run.map(node => node.textContent).join('')

    run.slice(1).forEach(node => node.remove())
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
