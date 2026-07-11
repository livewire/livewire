import { contextualizeExpression } from '../evaluator'
import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    if (! el.hasAttribute('wire:for')) return

    // Morphing initializes the incoming "to" tree in Alpine's clone mode. Binding
    // there would render template contents into the incoming HTML itself, so
    // only bind inside the live document...
    if (! el.isConnected) return

    if (el.tagName.toLowerCase() !== 'template') {
        console.warn('Livewire: wire:for can only be used on a <template> tag', el)
    }

    let expression = contextualizeForExpression(el.getAttribute('wire:for').trim(), el)

    // `x-for` reads its key expression off `_x_keyExpression`, but Alpine stores
    // that while processing the template's own attributes — after this early
    // binding runs. Store it ahead of time so keyed lists work...
    let keyExpression = el.getAttribute('wire:for:key') || el.getAttribute(':key') || el.getAttribute('x-bind:key')

    if (keyExpression) el._x_keyExpression = keyExpression

    // `wire:key` holds a static string everywhere else in Livewire, so it can't
    // serve as the per-item key expression a template loop needs...
    if (! keyExpression && el.hasAttribute('wire:key')) {
        console.warn('Livewire: wire:key is not supported on wire:for templates. Key each item with wire:for:key instead (e.g. wire:for:key="item.id")', el)
    }

    Alpine.bind(el, { 'x-for': expression })
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
