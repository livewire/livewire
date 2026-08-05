import Alpine from 'alpinejs'

let modifiers = ['hover', 'preserve-scroll', 'transition']

// Build every ordered combination of modifiers (e.g. `.hover.transition` and
// `.transition.hover`) so any authored order is picked up...
let modifierCombos = (function build(remaining, current) {
    return remaining.flatMap((modifier, i) => {
        let combo = [...current, modifier]

        return [combo, ...build([...remaining.slice(0, i), ...remaining.slice(i + 1)], combo)]
    })
})(modifiers, [])

// Export all wire:navigate variations for use in other files
export let wireNavigateSelectors = [
    '[wire\\:navigate]',
    ...modifierCombos.map(combo => `[wire\\:navigate\\.${combo.join('\\.')}]`),
]

// Combined selector for querying all wire:navigate elements
export let wireNavigateSelector = wireNavigateSelectors.join(', ')

// Attribute to Alpine directive mapping
let attributeMap = Object.fromEntries([
    ['wire:navigate', 'x-navigate'],
    ...modifierCombos.map(combo => [`wire:navigate.${combo.join('.')}`, `x-navigate.${combo.join('.')}`]),
])

// Register all selectors with Alpine
wireNavigateSelectors.forEach(selector => {
    Alpine.addInitSelector(() => selector)
})

Alpine.interceptInit(
    Alpine.skipDuringClone(el => {
        // Find which wire:navigate attribute this element has
        for (let [wireAttr, alpineDirective] of Object.entries(attributeMap)) {
            if (el.hasAttribute(wireAttr)) {
                Alpine.bind(el, { [alpineDirective]: true })
                break
            }
        }
    })
)

document.addEventListener('alpine:navigating', () => {
    // Before navigating away, we'll inscribe the latest state of each component
    // in their HTML so that upon return, they will have the latest state...
    Livewire.all().forEach(component => {
        component.inscribeSnapshotAndEffectsOnElement()
    })
})
