import { closestFragment, extractFragmentMetadataFromHtml, extractInnerHtmlFromFragmentHtml, findFragment } from '@/fragment'
import { interceptAction, interceptMessage } from '@/request'
import { morphFragment, morphIslandPart, getTagName } from '@/morph'
import { findComponentByEl } from '@/store'

interceptAction(({ action }) => {
    let origin = action.origin

    if (! origin) return

    let { el, directive } = origin

    // Check for wire:island with modifiers (e.g., wire:island.append="foo")
    let islandAttr = Array.from(el.attributes).find(attr => attr.name.startsWith('wire:island'))

    if (islandAttr) {
        let islandName = islandAttr.value

        // Parse modifiers from attribute name (e.g., "wire:island.append" -> ["append"])
        let attrParts = islandAttr.name.split('.')
        let isPrepend = attrParts.includes('prepend')
        let isAppend = attrParts.includes('append')

        let mode = isPrepend ? 'prepend' : (isAppend ? 'append' : 'morph')

        action.mergeMetadata({
            island: {
                name: islandName,
                mode: mode,
            }
        })

        return
    }

    let fragment = closestIsland(origin.el)

    if (! fragment) return

    action.mergeMetadata({
        island: {
            name: fragment.metadata.name,
            mode: 'morph',
        }
    })
})

interceptMessage(({ message, onSuccess, onStream }) => {
    onStream(async ({ json }) => {
        let { type, islandFragment } = json

        if (type !== 'island') return

        await renderIsland(message.component, islandFragment)
    })

    onSuccess(({ payload, onMorph }) => {
        onMorph(async () => {
            let fragments = []

            if (Object.prototype.hasOwnProperty.call(payload.effects, 'islandFragments') && payload.effects.islandFragments) {
                fragments = payload.effects.islandFragments
            }

            for (let fragmentHtml of fragments) {
                await renderIsland(message.component, fragmentHtml)
            }

            for (let part of payload.effects.islandParts || []) {
                try {
                    await renderIslandPart(message.component, part)
                } catch (error) {
                    // Invalid targets must be observable without interrupting
                    // the request's remaining morph/loading cleanup callbacks.
                    window.reportError(error)
                }
            }
        })
    })
})

export function closestIsland(el) {
    return closestFragment(el, {
        isMatch: ({ type }) => {
            return type === 'island'
        },
        hasReachedBoundary: ({ el }) => el.hasAttribute('wire:id'),
    })
}

export async function renderIsland(component, islandHtml) {
    let metadata = extractFragmentMetadataFromHtml(islandHtml)

    let fragment = findFragment(component.el, {
        isMatch: ({ type, token }) => {
            return type === metadata.type && token === metadata.token
        },
    })

    if (! fragment) return

    let incomingMetadata = extractFragmentMetadataFromHtml(islandHtml)
    let strippedContent = extractInnerHtmlFromFragmentHtml(islandHtml)

    let parentElementTag = getTagName(fragment.startMarkerNode.parentElement)

    let mode = incomingMetadata.mode || 'morph'

    if (mode === 'morph') {
        await morphFragment(component, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent)
    } else if (mode === 'append') {
        fragment.append(parentElementTag, strippedContent)
    } else if (mode === 'prepend') {
        fragment.prepend(parentElementTag, strippedContent)
    }
}

export async function renderIslandPart(component, part) {
    let { island, token, name, target, mode, html } = part
    let fail = reason => { throw new Error(`Livewire island part [${mode}] in island [${island}], part [${name}], target [${target}]: ${reason}`) }

    if (! ['morph', 'append', 'prepend', 'remove'].includes(mode)) fail('invalid mode')
    if (typeof target !== 'string' || target.trim() === '') fail('target must not be empty')

    let fragment = findFragment(component.el, {
        isMatch: metadata => metadata.type === 'island' && metadata.name === island && metadata.token === token,
        hasReachedBoundary: ({ el }) => el.hasAttribute('wire:id'),
    })

    if (! fragment || ! fragment.endMarkerNode) fail('island was not found')

    // One query per operation; normal island loads have no part indexing pass.
    let matches = Array.from(component.el.querySelectorAll(`[data-island-part="${CSS.escape(target)}"]`)).filter(el => {
        return findComponentByEl(el, false) === component
            && closestIsland(el)?.startMarkerNode === fragment.startMarkerNode
    })
    let inserting = mode === 'append' || mode === 'prepend'

    if (matches.length !== (inserting ? 0 : 1)) {
        fail(inserting ? 'target already exists' : `expected one target, found ${matches.length}`)
    }

    let to = null

    if (mode !== 'remove') {
        let range = document.createRange()
        range.selectNodeContents(fragment.startMarkerNode.parentElement)
        let incoming = range.createContextualFragment(html)
        let nodes = Array.from(incoming.childNodes).filter(node => node.nodeType !== Node.COMMENT_NODE && ! (node.nodeType === Node.TEXT_NODE && node.textContent.trim() === ''))

        if (nodes.length !== 1 || nodes[0].nodeType !== Node.ELEMENT_NODE) fail('incoming HTML must have exactly one root element')
        to = nodes[0]
        if (to.getAttribute('data-island-part') !== target) fail('incoming root does not match the target')
        if (to.hasAttribute('wire:id')) fail('the part root must belong to its parent component')
    }

    await morphIslandPart(component, fragment, matches[0], to, mode)
}
