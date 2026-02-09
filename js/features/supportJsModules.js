import { on } from '@/hooks'
import { interceptMessage } from '@/request/index'
import { getModuleUrl } from '@/utils'

let preloadedModules = new Map()

export async function preloadExistingModules() {
    let moduleUrl = getModuleUrl()

    if (! moduleUrl) return

    let promises = []

    findModulesInElements(document.querySelectorAll('[wire\\:id]')).forEach(({ name, hash }) => {
        let path = buildModulePath(name, hash)

        promises.push(
            import(/* @vite-ignore */ path).then(module => {
                preloadedModules.set(path, module)
            })
        )
    })

    await Promise.all(promises)
}

function buildModulePath(name, hash) {
    let encodedName = name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')
    return `${getModuleUrl()}/js/${encodedName}.js?v=${hash}`
}

function findModulesInElements(elements) {
    let modules = []

    elements.forEach(el => {
        let effectsAttr = el.getAttribute('wire:effects')

        if (! effectsAttr) return

        let effects = JSON.parse(effectsAttr)

        if (! effects.scriptModule) return

        let snapshotAttr = el.getAttribute('wire:snapshot')

        if (! snapshotAttr) return

        let snapshot = JSON.parse(snapshotAttr)

        modules.push({ name: snapshot.memo.name, hash: effects.scriptModule })
    })

    return modules
}

function findModulesInHtml(html) {
    let temp = document.createElement('div')
    temp.innerHTML = html

    return findModulesInElements(temp.querySelectorAll('[wire\\:effects]'))
}

// Intercept messages to pre-load script modules before morph
interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onEffect }) => {
        let modulesToLoad = []

        // Own module (for lazy-loaded components)
        if (payload.effects.scriptModule) {
            modulesToLoad.push({
                name: message.component.name,
                hash: payload.effects.scriptModule,
                isOwn: true
            })
        }

        // Child modules (scan response HTML for components with script modules)
        if (payload.effects.html) {
            findModulesInHtml(payload.effects.html).forEach(({ name, hash }) => {
                modulesToLoad.push({ name, hash, isOwn: false })
            })
        }

        if (modulesToLoad.length === 0) return

        // Start loading modules that aren't cached
        let loadPromises = []

        modulesToLoad.forEach(({ name, hash }) => {
            let path = buildModulePath(name, hash)

            if (! preloadedModules.has(path)) {
                loadPromises.push(
                    import(/* @vite-ignore */ path).then(module => {
                        preloadedModules.set(path, module)
                    })
                )
            }
        })

        // Set up onEffect to wait for modules and run own module if needed
        onEffect(async () => {
            // Wait for all modules to load
            await Promise.all(loadPromises)

            // Run the component's own module if it has one
            let ownModule = modulesToLoad.find(m => m.isOwn)

            if (ownModule) {
                let path = buildModulePath(ownModule.name, ownModule.hash)
                let module = preloadedModules.get(path)

                module.run.call(message.component.$wire, message.component.$wire, message.component.$wire.js)
            }
        })
    })
})

// Handle initial page load modules (scenario 1) - run synchronously since they're pre-loaded
on('effect', ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule

    if (! scriptModuleHash) return

    let path = buildModulePath(component.name, scriptModuleHash)

    // Only run if already pre-loaded (from initial page load)
    // Dynamic modules are handled by interceptMessage above
    if (preloadedModules.has(path)) {
        let module = preloadedModules.get(path)

        module.run.call(component.$wire, component.$wire, component.$wire.js)
    }
})

// Backwards compatibility - no longer needed
export function hasPendingModule(el) {
    return false
}

export function assetIsPendingFor(component) {
    return false
}

export function runAfterAssetIsLoadedFor(component, callback) {
    callback()
}
