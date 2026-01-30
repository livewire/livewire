import { WeakBag } from './utils'

// wire:model.blur defers client-side syncing until the input loses focus.
// Instead of updating $wire on every keystroke, the value only syncs
// when a blur event fires on the element.
//
// This creates a problem during form submission: the browser fires the
// submit event before blur, so the input hasn't updated $wire yet and
// its value is missing from the request payload.
//
// To solve this, we track elements with wire:model.blur and force them
// to sync by dispatching a blur event before actions run.

let elementsByComponent = new WeakBag

export function registerBlurElement(component, el) {
    elementsByComponent.add(component, el)
}

export function syncBlurElements(component) {
    elementsByComponent.each(component, el => {
        el.dispatchEvent(new Event('blur'))
    })
}
