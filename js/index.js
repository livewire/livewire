import { dispatchGlobal as dispatch, dispatchTo, on } from './events'
import { find, first, getByName, all } from './store'
import { start } from './lifecycle'
import { on as hook, trigger, triggerAsync } from './hooks'
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
    triggerAsync,
    dispatch,
    on,
    get navigate() {
        return Alpine.navigate
    }
}

let warnAboutMultipleInstancesOf = entity => console.warn(`Detected multiple instances of ${entity} running`)

if (window.Livewire) warnAboutMultipleInstancesOf('Livewire')
if (window.Alpine) warnAboutMultipleInstancesOf('Alpine')

// Register features...
import './features/index'

// Register directives...
import './directives/index'

// Make globals...
window.Livewire = Livewire
window.Alpine = Alpine

if (window.livewireScriptConfig === undefined) {
    window.Alpine.__fromLivewire = true

    document.addEventListener('DOMContentLoaded', () => {
        if (window.Alpine.__fromLivewire === undefined) {
            // If this is undefined, we know that an outside Alpine bundle
            // has been included on the page and will cause problems...
            warnAboutMultipleInstancesOf('Alpine')
        }

        // Start Livewire...
        Livewire.start()
    })
}

export { Livewire, Alpine }
