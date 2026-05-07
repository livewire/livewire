import { extractFragmentMetadataFromHtml, extractInnerHtmlFromFragmentHtml, findFragment } from '@/fragment'
import { interceptMessage } from '@/request'
import { findComponent } from '@/store'
import { morphFragment } from '@/morph'
import { on } from '@/hooks'

// Slot fragments that arrived before their target's slot markers existed in
// the DOM (e.g. the target is a lazy child whose placeholder hasn't been
// replaced yet). Keyed by the target component's id...
let pendingSlotFragments = {}

// Initial component mount: process any `slotFragments` effects delivered in
// the parent's full-page render. This is the path lazy components rely on...
on('effect', ({ component, effects }) => {
    let fragments = effects.slotFragments || []

    fragments.forEach(fragmentHtml => renderSlotOrStash(fragmentHtml))
})

// Subsequent messages: process this message's `slotFragments` effects, and
// retry any fragments that were stashed for this component's id (e.g. its
// markers have just appeared after a lazy load morph)...
interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(async () => {
            let fragments = payload.effects.slotFragments || []

            for (let fragmentHtml of fragments) {
                await renderSlotOrStash(fragmentHtml)
            }

            await applyPendingSlotFragments(message.component.id)
        })
    })
})

export async function renderSlot(component, fragmentHtml) {
    await renderSlotOrStash(fragmentHtml)
}

async function renderSlotOrStash(fragmentHtml) {
    let metadata = extractFragmentMetadataFromHtml(fragmentHtml)

    let targetComponent = findComponent(metadata.id, false)

    if (! targetComponent) return stashSlotFragment(metadata.id, fragmentHtml)

    let fragment = findFragment(targetComponent.el, {
        isMatch: ({ name, token }) => {
            return name === metadata.name && token === metadata.token
        },
    })

    if (! fragment) return stashSlotFragment(metadata.id, fragmentHtml)

    let strippedContent = extractInnerHtmlFromFragmentHtml(fragmentHtml)

    await morphFragment(targetComponent, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent)
}

function stashSlotFragment(componentId, fragmentHtml) {
    if (! pendingSlotFragments[componentId]) pendingSlotFragments[componentId] = []

    pendingSlotFragments[componentId].push(fragmentHtml)
}

async function applyPendingSlotFragments(componentId) {
    let pending = pendingSlotFragments[componentId]

    if (! pending) return

    delete pendingSlotFragments[componentId]

    for (let fragmentHtml of pending) {
        await renderSlotOrStash(fragmentHtml)
    }
}
