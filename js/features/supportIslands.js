import { interceptAction, interceptMessage } from '@/request'
import { Island } from '@/island'
import { morphFragment } from '@/morph'

interceptAction(({ action }) => {
    let origin = action.origin

    if (! origin) return

    let island = Island.closestIsland(origin)

    if (! island) return

    action.mergeMetadata(island.toMetadata())
})

interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(() => {
            let islands = payload.effects.islands || []

            islands.forEach(island => {
                let { name, html, mode } = island

                renderIsland(message.component, name, html, mode)
            })
        })
    })
})

export function renderIsland(component, key, html, mode = null) {
    let island = component.islands[key]

    mode ??= island.mode

    let { startNode, endNode } = findIslandComments(component.el, key)

    if (!startNode || !endNode) return

    let strippedContent = stripIslandComments(html, key)

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
        morphFragment(component, startNode, endNode, strippedContent)
    }
}



function isPlaceholderMarker(el) {
    return el.nodeType === 8 && el.textContent.match(/\[if ISLAND:[\w-]+:placeholder\]/)
}

function stripIslandComments(html, key) {
    // Remove the start and end comment markers
    let startComment = new RegExp(`<!--\\[if ISLAND:${key}(:placeholder)?\\]><\\!\\[endif\\]-->`)
    let endComment = new RegExp(`<!--\\[if ENDISLAND:${key}\\]><\\!\\[endif\\]-->`)

    // Strip out the comments from thehtml
    let stripped =html
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
