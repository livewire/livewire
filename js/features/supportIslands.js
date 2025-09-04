import { findComponent, hasComponent } from '@/store'
import { morphIsland } from '@/morph'
import { on } from '@/hooks'

on('stream', (payload) => {
    if (payload.type !== 'island') return

    let { id, name, content } = payload

    if (! hasComponent(id)) return

    let component = findComponent(id)

    streamIsland(component, key, content)
})

export function streamIsland(component, key, content) {
    renderIsland(component, key, content)
}

export function renderIsland(component, key, content, mode = null) {
    let island = component.islands[key]
    mode ??= island.mode

    let { startNode, endNode } = findIslandComments(component.el, key)

    if (!startNode || !endNode) return

    let strippedContent = stripIslandComments(content, key)

    let parentElement = startNode.parentElement
    let parentElementTag = parentElement ? parentElement.tagName.toLowerCase() : 'div'

    // If the start node is a placeholder marker, we need to replace the island regardless of the mode....
    if (isPlaceholderMarker(startNode)) {
        mode = 'replace'

        // Remove the placeholder marker from the start node...
        startNode.textContent = startNode.textContent.replace(':placeholder', '')
    }

    if (mode === 'append') {
        let container = document.createElement(parentElementTag)

        container.innerHTML = strippedContent

        // Insert each child node before the end node
        Array.from(container.childNodes).forEach(node => {
            endNode.parentNode.insertBefore(node, endNode)
        })

    } else if (mode === 'prepend') {
        let container = document.createElement(parentElementTag)

        container.innerHTML = strippedContent

        // Insert each child node after the start node in reverse order
        // to maintain correct ordering
        Array.from(container.childNodes)
            .reverse()
            .forEach(node => {
                startNode.parentNode.insertBefore(node, startNode.nextSibling)
            })
    } else {
        morphIsland(component, startNode, endNode, strippedContent)
    }
}

export function skipIslandContents(component, el, toEl, skipUntil) {
    if (isStartMarker(el) && isStartMarker(toEl)) {
        let key = extractIslandKey(toEl)
        let island = component.islands[key]
        let mode = island.mode
        let render = island.render

        if (['bypass', 'skip', 'once'].includes(render)) {
            skipUntil(node => isEndMarker(node))
        } else if (mode === 'prepend') {
            // Collect all siblings until end marker
            let sibling = toEl.nextSibling
            let siblings = []
            while (sibling && !isEndMarker(sibling)) {
                siblings.push(sibling)
                sibling = sibling.nextSibling
            }

            // Insert collected siblings after the start marker
            siblings.forEach(node => {
                el.parentNode.insertBefore(node.cloneNode(true), el.nextSibling)
            })

            skipUntil(node => isEndMarker(node))
        } else if (mode === 'append') {
            // Find end marker of fromEl
            let endMarker = el.nextSibling
            while (endMarker && !isEndMarker(endMarker)) {
                endMarker = endMarker.nextSibling
            }

            // Collect all siblings until end marker
            let sibling = toEl.nextSibling
            let siblings = []
            while (sibling && !isEndMarker(sibling)) {
                siblings.push(sibling)
                sibling = sibling.nextSibling
            }

            // Insert collected siblings before the end marker
            siblings.forEach(node => {
                endMarker.parentNode.insertBefore(node.cloneNode(true), endMarker)
            })

            skipUntil(node => isEndMarker(node))
        }
    }
}

export function closestIsland(component, el) {
    let current = el;

    while (current) {
        // Check previous siblings
        let sibling = current.previousSibling;

        let foundEndMarker = []
        while (sibling) {
            if (isEndMarker(sibling)) {
                // Keep iterating up until we find the start marker and skip it...
                foundEndMarker.push('a')
            }

            if (isStartMarker(sibling)) {
                if (foundEndMarker.length > 0) {
                    foundEndMarker.pop()
                } else {
                    let key = extractIslandKey(sibling)

                    return component.islands[key]
                }
            }

            sibling = sibling.previousSibling;
        }

        // No start marker found at this level or found end marker
        // Go up to parent unless we've hit the component root
        current = current.parentElement;

        if (current && current.hasAttribute('wire:id')) {
            break; // Stop at component root
        }
    }

    return null;
}

function isStartMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if ISLAND')
}

function isEndMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if ENDISLAND')
}

function extractIslandKey(el) {
    let key = el.textContent.match(/\[if ISLAND:([\w-]+)(?::placeholder)?\]/)?.[1]

    return key
}

function isPlaceholderMarker(el) {
    return el.nodeType === 8 && el.textContent.match(/\[if ISLAND:[\w-]+:placeholder\]/)
}

function stripIslandComments(content, key) {
    // Remove the start and end comment markers
    let startComment = new RegExp(`<!--\\[if ISLAND:${key}(:placeholder)?\\]><\\!\\[endif\\]-->`)
    let endComment = new RegExp(`<!--\\[if ENDISLAND:${key}\\]><\\!\\[endif\\]-->`)

    // Strip out the comments from the content
    let stripped = content
        .replace(startComment, '')
        .replace(endComment, '')

    return stripped.trim()
}

function findIslandComments(rootEl, key) {
    let startNode = null
    let endNode = null

    walkElements(rootEl, (el, skip) => {
        // Skip nested Livewire components
        if (el.hasAttribute && el.hasAttribute('wire:id') && el !== rootEl) {
            return skip()
        }

        // Check all child nodes (including text and comment nodes)
        Array.from(el.childNodes).forEach(node => {
            if (node.nodeType === Node.COMMENT_NODE) {
                if (node.textContent.match(new RegExp(`\\[if ISLAND:${key}(:placeholder)?\\]><\\!\\[endif\\]`))) {
                    startNode = node
                }

                if (node.textContent.match(new RegExp(`\\[if ENDISLAND:${key}\\]><\\!\\[endif\\]`))) {
                    endNode = node
                }
            }
        })
    })

    return { startNode, endNode }
}

function walkElements(el, callback) {
    let skip = false
    callback(el, () => skip = true)

    if (skip) return

    Array.from(el.children).forEach(child => {
        walkElements(child, callback)
    })
}
