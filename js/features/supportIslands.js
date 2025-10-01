import { interceptAction, interceptMessage } from '@/request'
import { morphFragment } from '@/morph'
import { closestFragment, extractFragmentMetadataFromHtml, extractInnerHtmlFromFragmentHtml, findFragment } from '@/fragment'

interceptAction(({ action }) => {
    let origin = action.origin

    if (! origin) return

    let fragment = closestFragment(origin.el, {
        isMatch: ({ type }) => {
            return type === 'island'
        },
        hasReachedBoundary: ({ el }) => {
            return el.hasAttribute('wire:id')
        },
    })

    if (! fragment) return

    action.mergeMetadata({
        island: {
            name: fragment.metadata.name,
            mode: 'morph',
        }
    })
})

interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(() => {
            let fragments = payload.effects.islandFragments || []

            fragments.forEach(fragmentHtml => {
                renderIsland(message.component, fragmentHtml)
            })
        })
    })
})

export function renderIsland(component, islandHtml) {
    let metadata = extractFragmentMetadataFromHtml(islandHtml)

    let fragment = findFragment(component.el, {
        isMatch: ({ type, name }) => {
            return type === metadata.type && name === metadata.name
        },
        hasReachedBoundary: ({ el }) => {
            return el.hasAttribute('wire:id')
        },
    })

    if (! fragment) return

    let strippedContent = extractInnerHtmlFromFragmentHtml(islandHtml)

    let parentElement = fragment.startMarkerNode.parentElement
    let parentElementTag = parentElement ? parentElement.tagName.toLowerCase() : 'div'

    mode = fragment.metadata.mode || 'morph'

    if (mode === 'morph') {
        morphFragment(component, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent)
    }

    return
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

}


function extractModeFromFragmentOpeningComment(el, defaultMode = 'morph') {
    return el.textContent.match(/\[if FRAGMENT:[\w-]+:(\w+)\]/)?.[1] ?? defaultMode
}