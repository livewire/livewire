import { extractFragmentMetadataFromHtml, extractInnerHtmlFromFragmentHtml, findFragment } from '@/fragment'
import { interceptMessage } from '@/request'
import { findComponent } from '@/store'
import { morphFragment } from '@/morph'
import { on } from '@/hooks'

let pending = {}

on('effect', ({ effects }) => (effects.slotFragments || []).forEach(f => renderSlot(null, f)))

interceptMessage(({ message, onSuccess, onStream }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(async () => {
            let fragments = payload.effects.slotFragments || []

            fragments.forEach(async fragmentHtml => {
                await renderSlot(message.component, fragmentHtml)
            })

            for (let f of pending[message.component.id] || []) await renderSlot(null, f)
            delete pending[message.component.id]
        })
    })
})

export async function renderSlot(component, fragmentHtml) {
    let metadata = extractFragmentMetadataFromHtml(fragmentHtml)

    let targetComponent = findComponent(metadata.id, false)

    let fragment = targetComponent && findFragment(targetComponent.el, {
        isMatch: ({ name, token }) => {
            return name === metadata.name && token === metadata.token
        },
    })

    if (! fragment) {
        (pending[metadata.id] ??= []).push(fragmentHtml)
        return
    }

    let strippedContent = extractInnerHtmlFromFragmentHtml(fragmentHtml)

    await morphFragment(targetComponent, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent)
}
