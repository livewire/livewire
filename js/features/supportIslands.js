import { closestFragment, extractFragmentMetadataFromHtml, extractInnerHtmlFromFragmentHtml, findFragment } from '@/fragment'
import { interceptAction, interceptMessage } from '@/request'
import { morphFragment, getTagName } from '@/morph'

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
    let streamedFragments = Promise.resolve()

    onStream(({ json }) => {
        let { type, islandFragment } = json

        if (type !== 'island') return

        streamedFragments = streamedFragments.then(() => renderIsland(message.component, islandFragment))
    })

    onSuccess(({ payload, onMorph }) => {
        onMorph(async () => {
            await streamedFragments

            let fragments = []

            if (Object.prototype.hasOwnProperty.call(payload.effects, 'islandFragments') && payload.effects.islandFragments) {
                fragments = payload.effects.islandFragments
            }

            for (let fragmentHtml of fragments) {
                await renderIsland(message.component, fragmentHtml)
            }
        // Island fragment effects represent server work that happened before the final root render...
        }, { order: -1 })
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
