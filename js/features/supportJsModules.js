import { on } from '@/hooks'
import { getModuleUrl } from '@/utils'
import Alpine from 'alpinejs'

let pendingComponentAssets = new WeakMap()

on('effect', ({ component, effects }) => {
    let scriptModuleHash

    if (Object.prototype.hasOwnProperty.call(effects, 'scriptModule')) {
        scriptModuleHash = effects.scriptModule
    }

    if (scriptModuleHash) {
        let encodedName = component.name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')
        let path = `${getModuleUrl()}/js/${encodedName}.js?v=${scriptModuleHash}`

        let pendingAsset = Alpine.reactive({
            loading: true,
            afterLoaded: [],
        })

        pendingComponentAssets.set(component, pendingAsset)

        let promise = import(/* @vite-ignore */ path).then(module => {
            module.run.call(component.$wire, component.$wire, component.$wire.js)
        })

        let finish = () => {
            pendingAsset.loading = false

            if (pendingComponentAssets.get(component) === pendingAsset) {
                pendingComponentAssets.delete(component)
            }

            let callbacks = pendingAsset.afterLoaded.splice(0)

            callbacks.forEach(callback => callback())
        }

        Alpine.deferInit(component.el, promise.finally(finish))
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
