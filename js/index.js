import { dispatchGlobal as dispatch, dispatchTo, on } from './features/supportEvents'
import { directive } from './directives'
import { find, first, getByName, all } from './store'
import { on as hook, trigger } from './events'
import { dispatch as doDispatch } from './utils'
import { start, stop, rescan } from './lifecycle'
import Alpine from 'alpinejs'

export let Livewire = {
    directive,
    dispatchTo,
    start,
    stop,
    rescan,
    first,
    find,
    getByName,
    all,
    hook,
    trigger,
    dispatch,
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

doDispatch(document, 'livewire:init')

// Start Livewire...
Livewire.start()

doDispatch(document, 'livewire:initialized')
