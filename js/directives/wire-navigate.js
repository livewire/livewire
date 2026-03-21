import Alpine from 'alpinejs'

// Export all wire:navigate variations for use in other files
export let wireNavigateSelectors = [
    '[wire\\:navigate]',
    '[wire\\:navigate\\.hover]',
    '[wire\\:navigate\\.preserve-scroll]',
    '[wire\\:navigate\\.preserve-scroll\\.hover]',
    '[wire\\:navigate\\.hover\\.preserve-scroll]',
    '[wire\\:navigate\\.transition]',
    '[wire\\:navigate\\.hover\\.transition]',
    '[wire\\:navigate\\.transition\\.hover]',
    '[wire\\:navigate\\.preserve-scroll\\.transition]',
    '[wire\\:navigate\\.transition\\.preserve-scroll]',
    '[wire\\:navigate\\.hover\\.preserve-scroll\\.transition]',
    '[wire\\:navigate\\.hover\\.transition\\.preserve-scroll]',
    '[wire\\:navigate\\.preserve-scroll\\.hover\\.transition]',
    '[wire\\:navigate\\.preserve-scroll\\.transition\\.hover]',
    '[wire\\:navigate\\.transition\\.hover\\.preserve-scroll]',
    '[wire\\:navigate\\.transition\\.preserve-scroll\\.hover]',
]

// Combined selector for querying all wire:navigate elements
export let wireNavigateSelector = wireNavigateSelectors.join(', ')

// Register all selectors with Alpine
wireNavigateSelectors.forEach(selector => {
    Alpine.addInitSelector(() => selector)
})

Alpine.interceptInit(
    Alpine.skipDuringClone(el => {
        let attr = Array.from(el.getAttributeNames()).find(a => a.startsWith('wire:navigate'))

        if (! attr) return

        Alpine.bind(el, { [attr.replace('wire:', 'x-')]: true })
    })
)

document.addEventListener('alpine:navigating', () => {
    // Before navigating away, we'll inscribe the latest state of each component
    // in their HTML so that upon return, they will have the latest state...
    Livewire.all().forEach(component => {
        component.inscribeSnapshotAndEffectsOnElement()
    })
})
