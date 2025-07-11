export function findRef(component, ref) {
    let refEl = component.el.querySelector(`[wire\\:ref="${ref}"]`)

    if (! refEl) return console.error(`Ref "${ref}" not found in component "${component.id}"`)

    return refEl.__livewire?.$wire
}
