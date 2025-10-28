import { closestFragment, extractFragmentMetadataFromHtml, extractInnerHtmlFromFragmentHtml, findFragment } from '@/fragment'
import { interceptAction, interceptMessage } from '@/request'
import { morphFragment } from '@/morph'

interceptAction(({ action }) => {
    let origin = action.origin

    if (! origin) return

    let el = origin.el

    let islandAttributeName = el.getAttribute('wire:island')
    let prependIslandAttributeName = el.getAttribute('wire:island.prepend')
    let appendIslandAttributeName = el.getAttribute('wire:island.append')

    let islandName = islandAttributeName || prependIslandAttributeName || appendIslandAttributeName

    if (islandName) {
        let mode = appendIslandAttributeName ? 'append' : (prependIslandAttributeName ? 'prepend' : 'morph')

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
    onStream(({ streamedJson }) => {
        let { type, islandFragment } = streamedJson

        if (type !== 'island') return

        renderIsland(message.component, islandFragment)
    })

    onSuccess(({ payload, onMorph }) => {
        onMorph(() => {
            let fragments = payload.effects.islandFragments || []

            fragments.forEach(fragmentHtml => {
                renderIsland(message.component, fragmentHtml)
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

export function renderIsland(component, islandHtml) {
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

    mode = incomingMetadata.mode || 'morph'

    if (mode === 'morph') {
        morphFragment(component, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent)
    } else if (mode === 'append') {
        fragment.append(parentElementTag, strippedContent)
    } else if (mode === 'prepend') {
        fragment.prepend(parentElementTag, strippedContent)
    }
}
