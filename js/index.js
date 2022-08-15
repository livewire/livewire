import { on } from './events'
import { first } from './state'
import { start } from './lifecycle'

let Livewire = {
    start,
    // @todo: legacy name, offer "on" as the new name?
    hook: on,
    on,
    first,
}

if (! window.Livewire) window.Livewire = Livewire
