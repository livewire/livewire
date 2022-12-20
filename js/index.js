import { find, first } from './state'
import { start } from './lifecycle'
import { emit, on } from './features/events'
import { synthetic, on as hook } from './synthetic/index'
import Alpine from 'alpinejs'

/**
 * This is the single entrypoint into "synthetic". Users can pass
 * either a full snapshot, rendered on the backend, or they can
 * pass a string identifier and request a snapshot via fetch.
 */
window.synthetic = synthetic

// @todo - do better. Currently this is here for Laravel dusk tests (waitForLivewire macro).
window.syntheticOn = hook

export let Livewire = {
    start,
    // @todo: legacy name, offer "on" as the new name?
    hook,
    on,
    emit,
    first,
    find,
}

if (window.Livewire) console.warn('Detected multiple instances of Livewire running')
if (window.Alpine) console.warn('Detected multiple instances of Alpine running')

window.Livewire = Livewire
window.Alpine = Alpine

Livewire.start()
