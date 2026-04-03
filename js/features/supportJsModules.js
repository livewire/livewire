import { on } from '@/hooks'
import { interceptMessage } from '@/request'
import { getModuleUrl } from '@/utils'

let moduleCache = new Map()
let pendingComponentAssets = new WeakMap()

let LOAD_TIMEOUT = 5000

function encodeName(name) {
    return name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')
}

function modulePath(name, hash) {
    return `${getModuleUrl()}/js/${encodeName(name)}.js?v=${hash}`
}

function cacheKey(name, hash) {
    return `${name}:${hash}`
}

async function importWithTimeout(path) {
    return Promise.race([
        import(/* @vite-ignore */ path),
        new Promise((_, reject) => setTimeout(() => reject(new Error(`Module load timed out: ${path}`)), LOAD_TIMEOUT)),
    ])
}

async function ensureCached(name, hash) {
    let key = cacheKey(name, hash)

    if (moduleCache.has(key)) return

    let path = modulePath(name, hash)

    try {
        let module = await importWithTimeout(path)
        moduleCache.set(key, module)
    } catch (e) {
        console.warn(`Livewire: Failed to load script module for "${name}"`, e)
    }
}

function discoverModulesInHtml(html) {
    if (! html) return []

    let template = document.createElement('template')
    template.innerHTML = html

    let modules = []

    template.content.querySelectorAll('[wire\\:effects]').forEach(el => {
        try {
            let effects = JSON.parse(el.getAttribute('wire:effects'))
            let snapshot = JSON.parse(el.getAttribute('wire:snapshot'))

            if (effects.scriptModule && snapshot?.memo?.name) {
                modules.push({ name: snapshot.memo.name, hash: effects.scriptModule })
            }
        } catch (e) {
            // Skip elements with unparseable attributes
        }
    })

    return modules
}

// Scenario 1: Pre-load all modules before Alpine.start()
export async function preloadInitialModules() {
    let imports = []

    document.querySelectorAll('[wire\\:id]').forEach(el => {
        try {
            let effects = JSON.parse(el.getAttribute('wire:effects'))
            let snapshot = JSON.parse(el.getAttribute('wire:snapshot'))

            if (effects?.scriptModule && snapshot?.memo?.name) {
                imports.push(ensureCached(snapshot.memo.name, effects.scriptModule))
            }
        } catch (e) {
            // Skip elements with unparseable attributes
        }
    })

    await Promise.allSettled(imports)
}

// Scenarios 2 and 3: Pre-load modules from AJAX responses before processEffects
interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onPrepare }) => {
        onPrepare(async () => {
            let imports = []

            let effects = payload.effects

            // The component's own module
            if (effects.scriptModule) {
                imports.push(ensureCached(message.component.name, effects.scriptModule))
            }

            // Child modules found in the response HTML
            let childModules = discoverModulesInHtml(effects.html)
            childModules.forEach(({ name, hash }) => {
                imports.push(ensureCached(name, hash))
            })

            // Child modules found in island fragments
            let islandFragments = effects.islandFragments || []
            islandFragments.forEach(fragmentHtml => {
                let fragmentModules = discoverModulesInHtml(fragmentHtml)
                fragmentModules.forEach(({ name, hash }) => {
                    imports.push(ensureCached(name, hash))
                })
            })

            await Promise.allSettled(imports)
        })
    })
})

// Scenario 4: Pre-load modules before Alpine re-inits after wire:navigate
document.addEventListener('alpine:navigating', (e) => {
    e.detail.onSwap(() => preloadInitialModules())
})

// Effect handler: run modules (from cache if available, otherwise import)
on('effect', ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule

    if (! scriptModuleHash) return

    let key = cacheKey(component.name, scriptModuleHash)

    // If the module is cached (pre-loaded), run it synchronously
    if (moduleCache.has(key)) {
        let module = moduleCache.get(key)
        module.run.call(component.$wire, component.$wire, component.$wire.js)
        return
    }

    // Fallback: module wasn't pre-loaded, load it async
    let encodedName = encodeName(component.name)
    let path = `${getModuleUrl()}/js/${encodedName}.js?v=${scriptModuleHash}`

    pendingComponentAssets.set(component, Alpine.reactive({
        loading: true,
        afterLoaded: [],
    }))

    import(/* @vite-ignore */ path).then(module => {
        module.run.call(component.$wire, component.$wire, component.$wire.js)

        pendingComponentAssets.get(component).loading = false
        pendingComponentAssets.get(component).afterLoaded.forEach(callback => callback())
        pendingComponentAssets.delete(component)
    }).catch(e => {
        console.warn(`Livewire: Failed to load script module for "${component.name}"`, e)

        if (pendingComponentAssets.has(component)) {
            pendingComponentAssets.get(component).loading = false
            pendingComponentAssets.get(component).afterLoaded.forEach(callback => callback())
            pendingComponentAssets.delete(component)
        }
    })
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
