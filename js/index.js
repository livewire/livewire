import { dispatchGlobal as dispatch, dispatchTo, on } from './events'
import { find, first, getByName, all } from './store'
import { start } from './lifecycle'
import { on as hook, trigger } from './hooks'
import { directive } from './directives'
import Alpine from 'alpinejs'

let Livewire = {
    directive,
    dispatchTo,
    start,
    first,
    find,
    getByName,
    all,
    hook,
    trigger,
    dispatch,
    on,
    get navigate() {
        return Alpine.navigate
    }
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

if (window.livewireScriptConfig === undefined) {
    document.addEventListener('DOMContentLoaded', () => {
        // Start Livewire...
        Livewire.start()
    })
}

export { Livewire, Alpine };
