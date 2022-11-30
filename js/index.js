import { first } from './state'
import { start } from './lifecycle'
import { synthetic, on } from './synthetic/index'

/**
 * This is the single entrypoint into "synthetic". Users can pass
 * either a full snapshot, rendered on the backend, or they can
 * pass a string identifier and request a snapshot via fetch.
 */
 window.synthetic = synthetic

 // @todo - do better. Currently this is here for Laravel dusk tests (waitForLivewire macro).
 window.syntheticOn = on

export let Livewire = {
    start,
    // @todo: legacy name, offer "on" as the new name?
    hook: on,
    on,
    first,
}

if (! window.Livewire) window.Livewire = Livewire

function monkeyPatchDomSetAttributeToAllowAtSymbols() {
    // Because morphdom may add attributes to elements containing "@" symbols
    // like in the case of an Alpine `@click` directive, we have to patch
    // the standard Element.setAttribute method to allow this to work.
    let original = Element.prototype.setAttribute

    let hostDiv = document.createElement('div')

    Element.prototype.setAttribute = function newSetAttribute(name, value) {
        if (! name.includes('@')) {
            return original.call(this, name, value)
        }

        hostDiv.innerHTML = `<span ${name}="${value}"></span>`

        let attr = hostDiv.firstElementChild.getAttributeNode(name)

        hostDiv.firstElementChild.removeAttributeNode(attr)

        this.setAttributeNode(attr)
    }
}

monkeyPatchDomSetAttributeToAllowAtSymbols()

Livewire.start()
