import { on } from '@/hooks'
import { getUriPrefix } from '@/utils'

let pendingComponentAssets = new WeakMap()

on('effect', ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule

    if (scriptModuleHash) {
        let encodedName = component.name.replace('.', '--').replace('::', '---').replace(':', '----')
        let path = `${getUriPrefix()}/js/${encodedName}.js?v=${scriptModuleHash}`

        pendingComponentAssets.set(component, Alpine.reactive({
            loading: true,
            afterLoaded: [],
        }))

        import(path).then(module => {
            module.run.call(component.$wire, component.$wire, component.$wire.js);

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