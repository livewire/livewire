import Alpine from 'alpinejs'

// Export all wire:navigate variations for use in other files
// Export all wire:navigate variations for use in other files
export let wireNavigateSelectors = [
    '[wire\\:navigate]',
    '[wire\\:navigate\\.hover]',
    '[wire\\:navigate\\.replace]',
    '[wire\\:navigate\\.replace\\.hover]',
    '[wire\\:navigate\\.preserve-scroll]',
    '[wire\\:navigate\\.replace\\.preserve-scroll]',
    '[wire\\:navigate\\.preserve-scroll\\.hover]',
    '[wire\\:navigate\\.replace\\.preserve-scroll\\.hover]',
    '[wire\\:navigate\\.hover\\.preserve-scroll]',
    '[wire\\:navigate\\.replace\\.hover\\.preserve-scroll]',
]

// Combined selector for querying all wire:navigate elements
export let wireNavigateSelector = wireNavigateSelectors.join(', ')

// Attribute to Alpine directive mapping
let attributeMap = {
    'wire:navigate': 'x-navigate',
    'wire:navigate.hover': 'x-navigate.hover',
    'wire:navigate.replace': 'x-navigate.replace',
    'wire:navigate.replace.hover': 'x-navigate.replace.hover',
    'wire:navigate.preserve-scroll': 'x-navigate.preserve-scroll',
    'wire:navigate.replace.preserve-scroll':
        'x-navigate.replace.preserve-scroll',
    'wire:navigate.preserve-scroll.hover': 'x-navigate.preserve-scroll.hover',
    'wire:navigate.replace.preserve-scroll.hover':
        'x-navigate.replace.preserve-scroll.hover',
    'wire:navigate.hover.preserve-scroll': 'x-navigate.hover.preserve-scroll',
    'wire:navigate.replace.hover.preserve-scroll':
        'x-navigate.replace.hover.preserve-scroll',
}

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
    }),
)

document.addEventListener('alpine:navigating', () => {
    // Before navigating away, we'll inscribe the latest state of each component
    // in their HTML so that upon return, they will have the latest state...
    Livewire.all().forEach(component => {
        component.inscribeSnapshotAndEffectsOnElement()
    })
})
