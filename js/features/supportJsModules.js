import { on } from '@/hooks'
import { findComponentByEl } from '@/store'
import { getModuleUrl } from '@/utils'
import Alpine from 'alpinejs'

let pendingComponentAssets = new WeakMap()
let pendingComponentCount = 0

// Alpine's morph clones each matched element via `Alpine.cloneNode(from, to)`;
// the detached `to` node can't see `_x_ignore` on the live root, so we mirror
// it manually. This runs for every element pair on every morph, so bail fast
// when nothing's pending — and resolve via `findComponentByEl`, not `.closest`,
// so slot-forwarded content defers to the component that owns it, not its
// nearest DOM ancestor.
Alpine.interceptClone((from, to) => {
    if (pendingComponentCount === 0) return
    if (! from || from.nodeType !== 1) return

    let component = findComponentByEl(from, false)

    if (component && assetIsPendingFor(component)) {
        to._x_ignore = true
    }
})

on('effect', ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule

    if (scriptModuleHash) {
        let encodedName = component.name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')
        let path = `${getModuleUrl()}/js/${encodedName}.js?v=${scriptModuleHash}`

        // Already initialised (e.g. lazy hydration) — block the subtree until
        // the module loads, so `x-data` doesn't evaluate before `Alpine.data()`.
        let alreadyInitialised = component.el._x_marker

        if (alreadyInitialised) {
            component.el._x_ignore = true
        }

        pendingComponentAssets.set(component, Alpine.reactive({
            loading: true,
            afterLoaded: [],
        }))

        pendingComponentCount++

        import(/* @vite-ignore */ path).then(module => {
            module.run.call(component.$wire, component.$wire, component.$wire.js);
        }).finally(() => {
            // Runs on success or failure — a failed import leaves `Alpine.data()`
            // unregistered, so the dependent expression will throw, but that beats
            // leaving the whole component's directives permanently un-initialised.
            if (alreadyInitialised && component.el.isConnected) {
                delete component.el._x_ignore
                delete component.el._x_marker
                Alpine.initTree(component.el)
            }

            pendingComponentAssets.get(component).loading = false
            pendingComponentAssets.get(component).afterLoaded.forEach(callback => callback())
            pendingComponentAssets.delete(component)
            pendingComponentCount--
        });
    }
})

export function assetIsPendingFor(component) {
    return pendingComponentAssets.has(component) && pendingComponentAssets.get(component).loading
}

export function runAfterAssetIsLoadedFor(component, callback) {
    if (assetIsPendingFor(component)) {
        pendingComponentAssets.get(component).afterLoaded.push(() => callback())
    } else {
        callback()
    }
}