import { on } from '@/hooks'
import { getModuleUrl } from '@/utils'
import Alpine from 'alpinejs'

let pendingComponentAssets = new WeakMap()

let preloadedModules = new Map()

export async function preloadExistingModules() {
    let moduleUrl = getModuleUrl()

    if (! moduleUrl) return

    let promises = []

    document.querySelectorAll('[wire\\:id]').forEach(el => {
        let effectsAttr = el.getAttribute('wire:effects')

        if (! effectsAttr) return

        let effects = JSON.parse(effectsAttr)

        if (! effects.scriptModule) return

        let snapshotAttr = el.getAttribute('wire:snapshot')

        if (! snapshotAttr) return

        let snapshot = JSON.parse(snapshotAttr)
        let name = snapshot.memo.name
        let encodedName = name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')
        let path = `${moduleUrl}/js/${encodedName}.js?v=${effects.scriptModule}`

        promises.push(
            import(/* @vite-ignore */ path).then(module => {
                preloadedModules.set(path, module)
            })
        )
    })

    await Promise.all(promises)
}

on('effect', ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule

    if (scriptModuleHash) {
        let encodedName = component.name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')
        let path = `${getModuleUrl()}/js/${encodedName}.js?v=${scriptModuleHash}`

        if (preloadedModules.has(path)) {
            let module = preloadedModules.get(path)

            module.run.call(component.$wire, component.$wire, component.$wire.js)

            preloadedModules.delete(path)
        } else {
            pendingComponentAssets.set(component, Alpine.reactive({
                loading: true,
                afterLoaded: [],
            }))

            import(/* @vite-ignore */ path).then(module => {
                module.run.call(component.$wire, component.$wire, component.$wire.js)

                pendingComponentAssets.get(component).loading = false
                pendingComponentAssets.get(component).afterLoaded.forEach(callback => callback())
                pendingComponentAssets.delete(component)

                Alpine.initTree(component.el)
            });
        }
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
