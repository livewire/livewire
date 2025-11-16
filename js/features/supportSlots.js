import { extractFragmentMetadataFromHtml, extractInnerHtmlFromFragmentHtml, findFragment } from '@/fragment'
import { interceptMessage } from '@/request'
import { findComponent } from '@/store'
import { morphFragment } from '@/morph'

interceptMessage(({ message, onSuccess, onStream }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(() => {
            let fragments = payload.effects.slotFragments || []

            fragments.forEach(fragmentHtml => {
                renderSlot(message.component, fragmentHtml)
            })
        })
    })
})

export function renderSlot(component, fragmentHtml) {
    let metadata = extractFragmentMetadataFromHtml(fragmentHtml)

    let targetComponent = findComponent(metadata.id)

    let fragment = findFragment(targetComponent.el, {
        isMatch: ({ name, token }) => {
            return name === metadata.name && token === metadata.token
        },
    })

    if (! fragment) return

    let strippedContent = extractInnerHtmlFromFragmentHtml(fragmentHtml)

    morphFragment(targetComponent, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent)
}
