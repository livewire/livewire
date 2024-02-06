import Alpine from 'alpinejs'

Alpine.addInitSelector(() => `[wire\\:navigate]`)
Alpine.addInitSelector(() => `[wire\\:navigate\\.hover]`)

Alpine.interceptInit(
    Alpine.skipDuringClone(el => {
        if (el.hasAttribute('wire:navigate')) {
            Alpine.bind(el, { ['x-navigate']: true })
        } else if (el.hasAttribute('wire:navigate.hover')) {
            Alpine.bind(el, { ['x-navigate.hover']: true })
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
