import { contextualizeExpression } from '../evaluator'
import Alpine from 'alpinejs'

// Skipped during clone-mode init (morphing seeds incoming trees that way) —
// rendering template contents there would leak stray elements into the page...
Alpine.interceptInit(
    Alpine.skipDuringClone(el => {
        if (! el.hasAttribute('wire:for')) return

        if (el.tagName.toLowerCase() !== 'template') {
            console.warn('Livewire: wire:for can only be used on a <template> tag', el)

            return
        }

        let expression = contextualizeForExpression(el.getAttribute('wire:for').trim(), el)

        let keyExpression = el.getAttribute('wire:for:key') || el.getAttribute(':key') || el.getAttribute('x-bind:key')

        // `wire:key` holds a static string everywhere else in Livewire, so it can't
        // serve as the per-item key expression a template loop needs...
        if (! keyExpression && el.hasAttribute('wire:key')) {
            console.warn('Livewire: wire:key is not supported on wire:for templates. Key each item with wire:for:key instead (e.g. wire:for:key="item.id")', el)
        }

        Alpine.bind(el, {
            // Alpine processes `bind` before `for`, storing the key expression
            // where `x-for` reads per-item keys from...
            ...(keyExpression ? { 'x-bind:key': keyExpression } : {}),
            'x-for': expression,
        })
    })
)

// Split on the same "aliases in items" grammar as Alpine's own x-for parser
// (parseForExpression), then rewrite only the items half to target `$wire` —
// the iterator aliases on the left are declarations, not property lookups...
function contextualizeForExpression(expression, el) {
    let match = expression.match(/([\s\S]*?)\s+(in|of)\s+([\s\S]*)/)

    if (! match) return expression

    let [, aliases, keyword, items] = match

    return `${aliases} ${keyword} ${contextualizeExpression(items, el)}`
}
