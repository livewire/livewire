import { synthetic, on as hook } from './synthetic/index'
import { emit, on } from './features/events'
import { directive } from './directives'
import { find, first } from './state'
import { start } from './lifecycle'
import Alpine from 'alpinejs'

// Livewire global...
export let Livewire = {
    directive,
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
