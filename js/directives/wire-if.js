import { evaluateActionExpression } from '../evaluator'
import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    // Alpine seeds an incoming morph's detached "to" tree by initializing a clone
    // of it. Binding `x-if` there would render template contents into the
    // incoming HTML itself, so only bind inside the live document...
    if (! el.isConnected) return

    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:if')) {
            let { name, value } = el.attributes[i]

            if (el.tagName.toLowerCase() !== 'template') {
                console.warn('Livewire: wire:if can only be used on a <template> tag', el)
            }

            let modifierString = name.split('wire:if')[1]

            let expression = value.trim()

            Alpine.bind(el, {
                ['x-if' + modifierString]() {
                    return evaluateActionExpression(el, expression)
                }
            })
        }
    }
})
