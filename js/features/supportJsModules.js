import { on } from '@/hooks'
import { findComponentByEl } from '@/store'
import { getModuleUrl } from '@/utils'
import Alpine from 'alpinejs'

let pendingComponentAssets = new WeakMap()

// When Alpine's morph plugin clones a server-rendered element into the live
// tree it runs `Alpine.cloneNode(from, to)`, which calls `initTree(to)` on the
// detached `to` element. `_x_ignore` on the live `from` doesn't reach it, so
// expressions like `x-data="myComponent"` evaluate before the script module
// has loaded. Mirror the ignore flag onto `to` for any element whose live
// counterpart sits under a component with a pending module.
Alpine.interceptClone((from, to) => {
    if (! from || from.nodeType !== 1) return

    let root = from.closest('[wire\\:id]')
    if (! root) return

    let component = findComponentByEl(root, false)
    if (component && assetIsPendingFor(component)) {
        to._x_ignore = true
    }
})

on('effect', ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule

    if (scriptModuleHash) {
        let encodedName = component.name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')
        let path = `${getModuleUrl()}/js/${encodedName}.js?v=${scriptModuleHash}`

        // If Alpine has already initialised this component (i.e. an update or
        // lazy load, not a fresh init), block its subtree from initialising
        // until the module is loaded. Otherwise children morphed in by the
        // response will run `x-data` before `Alpine.data()` is registered.
        let alreadyInitialised = component.el && component.el._x_marker

        if (alreadyInitialised) {
            component.el._x_ignore = true
        }

        pendingComponentAssets.set(component, Alpine.reactive({
            loading: true,
            afterLoaded: [],
        }))

        import(/* @vite-ignore */ path).then(module => {
            module.run.call(component.$wire, component.$wire, component.$wire.js);

            if (alreadyInitialised && component.el && component.el.isConnected) {
                delete component.el._x_ignore
                // Clear the marker so initTree re-processes root's directives,
                // picking up `x-data` (and similar) that morph added during the
                // ignored window.
                delete component.el._x_marker
                Alpine.initTree(component.el)
            }

            pendingComponentAssets.get(component).loading = false
            pendingComponentAssets.get(component).afterLoaded.forEach(callback => callback())
            pendingComponentAssets.delete(component)
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