import { contextualizeExpression } from '../evaluator'
import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    // Alpine seeds an incoming morph's detached "to" tree by initializing a clone
    // of it. Binding `x-for` there would render template contents into the
    // incoming HTML itself, so only bind inside the live document...
    if (! el.isConnected) return

    for (let i = 0; i < el.attributes.length; i++) {
        let { name, value } = el.attributes[i]

        // Match precisely so `wire:for:key` isn't mistaken for the directive itself...
        if (name !== 'wire:for' && ! name.startsWith('wire:for.')) continue

        if (el.tagName.toLowerCase() !== 'template') {
            console.warn('Livewire: wire:for can only be used on a <template> tag', el)
        }

        let modifierString = name.split('wire:for')[1]

        let expression = contextualizeForExpression(value.trim(), el)

        // `x-for` reads its `:key` expression off `_x_keyExpression`, but Alpine
        // stores that while processing the template's own attributes — after this
        // early binding runs. Store it ahead of time so keyed lists work...
        let keyExpression = el.getAttribute('wire:for:key') || el.getAttribute(':key') || el.getAttribute('x-bind:key')

        if (keyExpression && ! el._x_keyExpression) el._x_keyExpression = keyExpression

        // `wire:key` holds a static string everywhere else, so it can't serve as
        // the per-item key expression a template loop needs. Steer muscle memory
        // toward the right attribute instead of silently falling back to index keys...
        if (! keyExpression && el.hasAttribute('wire:key')) {
            console.warn('Livewire: wire:key is not supported on wire:for templates. Use wire:for:key to key each item with a per-item expression (e.g. wire:for:key="item.id")', el)
        }

        Alpine.bind(el, {
            ['x-for' + modifierString]: expression,
        })
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
