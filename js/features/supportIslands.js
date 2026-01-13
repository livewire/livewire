import { closestFragment, extractFragmentMetadataFromHtml, extractInnerHtmlFromFragmentHtml, findFragment } from '@/fragment'
import { interceptAction, interceptMessage } from '@/request'
import { morphFragment } from '@/morph'

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
    onStream(({ json }) => {
        let { type, islandFragment } = json

        if (type !== 'island') return

        renderIsland(message.component, islandFragment)
    })

    onSuccess(({ payload, onMorph }) => {
        onMorph(async () => {
            let fragments = payload.effects.islandFragments || []

            fragments.forEach(async fragmentHtml => {
                await renderIsland(message.component, fragmentHtml)
            })
        })
    })
})

export function closestIsland(el) {
    return closestFragment(el, {
        isMatch: ({ type }) => {
            return type === 'island'
        },
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

    let parentElement = fragment.startMarkerNode.parentElement
    let parentElementTag = parentElement ? parentElement.tagName.toLowerCase() : 'div'

    let mode = incomingMetadata.mode || 'morph'

    if (mode === 'morph') {
        await morphFragment(component, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent)
    } else if (mode === 'append') {
        fragment.append(parentElementTag, strippedContent)
    } else if (mode === 'prepend') {
        fragment.prepend(parentElementTag, strippedContent)
    }
}
