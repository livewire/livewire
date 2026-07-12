import { evaluateActionExpression } from '../evaluator'
import Alpine from 'alpinejs'

// Skipped during clone-mode init (morphing seeds incoming trees that way) —
// rendering template contents there would leak stray elements into the page...
Alpine.interceptInit(
    Alpine.skipDuringClone(el => {
        if (! el.hasAttribute('wire:if')) return

        if (el.tagName.toLowerCase() !== 'template') {
            console.warn('Livewire: wire:if can only be used on a <template> tag', el)

            return
        }

        let expression = el.getAttribute('wire:if').trim()

        Alpine.bind(el, {
            ['x-if']() {
                return evaluateActionExpression(el, expression)
            }
        })
    })
)
