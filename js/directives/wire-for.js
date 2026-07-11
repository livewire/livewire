import { contextualizeExpression } from '../evaluator'
import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    // Alpine seeds an incoming morph's detached "to" tree by initializing a clone
    // of it. Binding `x-for` there would render template contents into the
    // incoming HTML itself, so only bind inside the live document...
    if (! el.isConnected) return

    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:for')) {
            let { name, value } = el.attributes[i]

            if (el.tagName.toLowerCase() !== 'template') {
                console.warn('Livewire: wire:for can only be used on a <template> tag', el)
            }

            let modifierString = name.split('wire:for')[1]

            let expression = contextualizeForExpression(value.trim(), el)

            // `x-for` reads its `:key` expression off `_x_keyExpression`, but Alpine
            // stores that while processing the template's own attributes — after this
            // early binding runs. Store it ahead of time so keyed lists work. Also
            // accept `wire:key` as an alias since that's the Livewire convention...
            let keyExpression = el.getAttribute(':key') || el.getAttribute('x-bind:key') || el.getAttribute('wire:key')

            if (keyExpression && ! el._x_keyExpression) el._x_keyExpression = keyExpression

            Alpine.bind(el, {
                ['x-for' + modifierString]: expression,
            })
        }
    }
})

// `x-for` parses its expression string directly ("item in items"), so it can't be
// evaluated lazily behind a function like `wire:show`/`wire:if`. Instead, rewrite
// only the items half to target `$wire` ("item in $wire.items") — the iterator
// aliases on the left are declarations, not component property lookups...
function contextualizeForExpression(expression, el) {
    let match = expression.match(/([\s\S]*?)\s+(?:in|of)\s+([\s\S]*)/)

    if (! match) return contextualizeExpression(expression, el)

    let [, aliases, items] = match

    return `${aliases} in ${contextualizeExpression(items, el)}`
}
