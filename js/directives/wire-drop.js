import { callAndClearComponentDebounces } from '@/debounce'
import { evaluateActionExpression } from '@/evaluator'
import { setNextActionOrigin } from '@/request'
import { directive } from '@/directives'
import { filesFromEvent } from '@/utils'

// A first-class dropzone. A plain `drop` listener never fires unless
// `dragover` is cancelled, so this directive owns the full drag lifecycle:
// it reflects the drag state as a `data-dragging` attribute for styling
// and evaluates the expression on drop.
//
// The `.file` modifier scopes the whole directive to drags carrying files:
// only file drags show `data-dragging`, and only file drops evaluate —
// dragging selected text over the element does nothing...
directive('drop', ({ el, directive, component, cleanup }) => {
    let fileOnly = directive.modifiers.includes('file')

    // The `.window` modifier accepts drops anywhere on the page —
    // `data-dragging` still lands on this element so overlays have
    // a styling anchor...
    let target = directive.modifiers.includes('window') ? window : el

    // Drags fire enter/leave pairs for every child element crossed, so
    // track depth and only clear the attribute when the drag truly leaves...
    let depth = 0

    let engages = e => {
        if (! e.dataTransfer) return false

        return fileOnly ? Array.from(e.dataTransfer.types || []).includes('Files') : true
    }

    let clearDragging = () => { depth = 0; el.removeAttribute('data-dragging') }

    let onDragenter = e => {
        if (! engages(e)) return

        e.preventDefault()

        depth++

        el.setAttribute('data-dragging', '')
    }

    // Cancelling dragover is what makes the element a valid drop target...
    let onDragover = e => {
        if (! engages(e)) return

        e.preventDefault()
    }

    let onDragleave = e => {
        if (! engages(e)) return

        depth = Math.max(0, depth - 1)

        if (depth === 0) el.removeAttribute('data-dragging')
    }

    let onDrop = e => {
        clearDragging()

        if (! engages(e)) return

        // A dropped file's default is "navigate away and open it" — always
        // cancel that. Other drops keep their default (text drops into
        // inputs, for example) unless the expression prevents it...
        if (filesFromEvent(e)?.length > 0) e.preventDefault()

        directive.eventContext = e
        directive.wire = component.$wire

        callAndClearComponentDebounces(component, () => {
            setNextActionOrigin({ el, directive })

            evaluateActionExpression(el, directive.expression, { scope: { $event: e } })
        })
    }

    target.addEventListener('dragenter', onDragenter)
    target.addEventListener('dragover', onDragover)
    target.addEventListener('dragleave', onDragleave)
    target.addEventListener('drop', onDrop)

    cleanup(() => {
        target.removeEventListener('dragenter', onDragenter)
        target.removeEventListener('dragover', onDragover)
        target.removeEventListener('dragleave', onDragleave)
        target.removeEventListener('drop', onDrop)
    })
})
