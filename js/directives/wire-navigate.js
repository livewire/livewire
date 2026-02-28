import Alpine from 'alpinejs'

// Export all wire:navigate variations for use in other files
export let wireNavigateSelectors = [
    '[wire\\:navigate]',
    '[wire\\:navigate\\.hover]',
    '[wire\\:navigate\\.preserve-scroll]',
    '[wire\\:navigate\\.preserve-scroll\\.hover]',
    '[wire\\:navigate\\.hover\\.preserve-scroll]',
    '[wire\\:navigate\\.transition]',
    '[wire\\:navigate\\.transition\\.hover]',
    '[wire\\:navigate\\.hover\\.transition]',
    '[wire\\:navigate\\.transition\\.preserve-scroll]',
    '[wire\\:navigate\\.preserve-scroll\\.transition]',
    '[wire\\:navigate\\.transition\\.hover\\.preserve-scroll]',
    '[wire\\:navigate\\.transition\\.preserve-scroll\\.hover]',
    '[wire\\:navigate\\.hover\\.transition\\.preserve-scroll]',
    '[wire\\:navigate\\.hover\\.preserve-scroll\\.transition]',
    '[wire\\:navigate\\.preserve-scroll\\.transition\\.hover]',
    '[wire\\:navigate\\.preserve-scroll\\.hover\\.transition]',
]

// Combined selector for querying all wire:navigate elements
export let wireNavigateSelector = wireNavigateSelectors.join(', ')

// Attribute to Alpine directive mapping
let attributeMap = {
    'wire:navigate': 'x-navigate',
    'wire:navigate.hover': 'x-navigate.hover',
    'wire:navigate.preserve-scroll': 'x-navigate.preserve-scroll',
    'wire:navigate.preserve-scroll.hover': 'x-navigate.preserve-scroll.hover',
    'wire:navigate.hover.preserve-scroll': 'x-navigate.hover.preserve-scroll',
    'wire:navigate.transition': 'x-navigate.transition',
    'wire:navigate.transition.hover': 'x-navigate.transition.hover',
    'wire:navigate.hover.transition': 'x-navigate.hover.transition',
    'wire:navigate.transition.preserve-scroll': 'x-navigate.transition.preserve-scroll',
    'wire:navigate.preserve-scroll.transition': 'x-navigate.preserve-scroll.transition',
    'wire:navigate.transition.hover.preserve-scroll': 'x-navigate.transition.hover.preserve-scroll',
    'wire:navigate.transition.preserve-scroll.hover': 'x-navigate.transition.preserve-scroll.hover',
    'wire:navigate.hover.transition.preserve-scroll': 'x-navigate.hover.transition.preserve-scroll',
    'wire:navigate.hover.preserve-scroll.transition': 'x-navigate.hover.preserve-scroll.transition',
    'wire:navigate.preserve-scroll.transition.hover': 'x-navigate.preserve-scroll.transition.hover',
    'wire:navigate.preserve-scroll.hover.transition': 'x-navigate.preserve-scroll.hover.transition',
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
    })
)

document.addEventListener('alpine:navigating', () => {
    // Before navigating away, we'll inscribe the latest state of each component
    // in their HTML so that upon return, they will have the latest state...
    Livewire.all().forEach(component => {
        component.inscribeSnapshotAndEffectsOnElement()
    })
})
