import { on } from '@/hooks'
import { findComponentByEl } from '@/store'
import { getModuleUrl } from '@/utils'
import Alpine from 'alpinejs'

let pendingComponentAssets = new WeakMap()
let pendingComponentCount = 0

// When Alpine's morph plugin clones a server-rendered element into the live
// tree it runs `Alpine.cloneNode(from, to)`, which calls `initTree(to)` on the
// detached `to` element. `_x_ignore` on the live `from` doesn't reach it, so
// expressions like `x-data="myComponent"` evaluate before the script module
// has loaded. Mirror the ignore flag onto `to` for any element whose live
// counterpart sits under a component with a pending module.
//
// This runs for every element pair morphed on every request, so bail before
// touching the DOM unless something is actually pending. `findComponentByEl`
// (not `.closest('[wire\\:id]')`) is required here so that elements forwarded
// into a child through a slot still resolve to the component that owns them.
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

        pendingComponentCount++

        import(/* @vite-ignore */ path).then(module => {
            module.run.call(component.$wire, component.$wire, component.$wire.js);
        }).finally(() => {
            // Runs whether the import succeeded or failed. A failed import (e.g. a
            // 404 on the module route) leaves `Alpine.data()` unregistered, so any
            // expression depending on it will throw once we stop ignoring this
            // element — but that's preferable to leaving the entire component's
            // directives permanently un-initialised because one script failed.
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