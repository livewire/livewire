import { emit, emitTo, on } from './features/events'
import { directive } from './directives'
import { find, first } from './store'
import { on as hook } from './events'
import { start } from './lifecycle'
import Alpine from 'alpinejs'
import { onErrorResponse } from './response'

export let Livewire = {
    onErrorResponse,
    directive,
    emitTo,
    start,
    first,
    find,
    hook,
    emit,
    on,
}

if (window.Livewire) console.warn('Detected multiple instances of Livewire running')
if (window.Alpine) console.warn('Detected multiple instances of Alpine running')

// Register features...
import './features/index'

// Register directives...
import './directives/index'

// Make globals...
window.Livewire = Livewire
window.Alpine = Alpine

// Start Livewire...
Livewire.start()
