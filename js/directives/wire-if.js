import { evaluateActionExpression } from '../evaluator'
import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    if (! el.hasAttribute('wire:if')) return

    // Morphing initializes the incoming "to" tree in Alpine's clone mode. Binding
    // there would render template contents into the incoming HTML itself, so
    // only bind inside the live document...
    if (! el.isConnected) return

    if (el.tagName.toLowerCase() !== 'template') {
        console.warn('Livewire: wire:if can only be used on a <template> tag', el)
    }

    let expression = el.getAttribute('wire:if').trim()

    Alpine.bind(el, {
        ['x-if']() {
            return evaluateActionExpression(el, expression)
        }
    })
})
