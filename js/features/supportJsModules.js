import { on } from '@/hooks'
import { getModuleUrl } from '@/utils'
import Alpine from 'alpinejs'

let pendingComponentAssets = new WeakMap()
let pendingComponentCount = 0

Alpine.interceptClone((from, to) => {
    if (pendingComponentCount === 0) return
    if (! from || from.nodeType !== 1) return

    let component = from.closest('[wire\\:id]')?.__livewire

    if (component && assetIsPendingFor(component)) {
        to._x_ignore = true
    }
})

on('effect', ({ component, effects }) => {
    let scriptModuleHash

    if (Object.prototype.hasOwnProperty.call(effects, 'scriptModule')) {
        scriptModuleHash = effects.scriptModule
    }

    if (scriptModuleHash) {
        let encodedName = component.name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')
        let path = `${getModuleUrl()}/js/${encodedName}.js?v=${scriptModuleHash}`
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
            module.run.call(component.$wire, component.$wire, component.$wire.js)

            let pendingAsset = pendingComponentAssets.get(component)

            pendingAsset.loading = false
            pendingComponentAssets.delete(component)
            pendingComponentCount--

            if (alreadyInitialised && component.el.isConnected) {
                delete component.el._x_ignore
                delete component.el._x_marker
                Alpine.initTree(component.el)
            }

            pendingAsset.afterLoaded.forEach(callback => callback())
        })
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
