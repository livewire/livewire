import { on } from '@/hooks'
import { interceptMessage } from '@/request'
import { getModuleUrl } from '@/utils'
import Alpine from 'alpinejs'

let pendingComponentAssets = new WeakMap()

let morphGates = new WeakMap()

// A component's script module is fetched asynchronously, but Alpine walks the
// DOM synchronously — left alone, Alpine evaluates the component's markup
// before the module's Alpine.data() and $js registrations exist. So while the
// module loads, the component's tree is suspended via Alpine.deferInit():
// nothing inside initializes until the module has run, and the rest of the
// page carries on. Scripts therefore always run with the component's markup
// in the DOM, but before Alpine has initialized it...
on('effect', ({ component, effects, request }) => {
    // A response that's about to mount new children with script modules names
    // them ahead of the morph — start fetching so their modules are warm (or
    // already loaded) by the time the children hit the page. Purely a latency
    // optimization: each child still imports its own module when it mounts...
    if (effects.childScriptModules) {
        effects.childScriptModules.forEach(({ name, hash }) => {
            import(/* @vite-ignore */ modulePath(name, hash)).catch(() => {})
        })
    }

    let scriptModuleHash

    if (Object.prototype.hasOwnProperty.call(effects, 'scriptModule')) {
        scriptModuleHash = effects.scriptModule
    }

    if (! scriptModuleHash) return

    let path = modulePath(component.name, scriptModuleHash)

    let pending = Alpine.reactive({
        loading: true,
        afterLoaded: [],
    })

    pendingComponentAssets.set(component, pending)

    // Start fetching immediately, but hold the module's execution until this
    // response's morph has landed (a lazy component's real markup arrives in
    // the same response as its scriptModule effect)...
    let modulePromise = import(/* @vite-ignore */ path)

    let ready = Promise.all([modulePromise, morphGateFor(component, request)])
        .then(([module]) => {
            // The component may have been removed while its module was in
            // flight — don't run the script for a component that's no longer
            // on the page...
            if (! component.el.isConnected) return

            module.run.call(component.$wire, component.$wire, component.$wire.js)

            pending.loading = false
            pending.afterLoaded.forEach(callback => callback())
        })
        .catch(error => {
            // A module that fails to load or run must not leave the component's
            // tree suspended forever. Surface the error, then let the tree
            // initialize without it...
            console.error(`Livewire: The script module for the [${component.name}] component failed:`, error)
        })
        .finally(() => {
            pending.loading = false

            pendingComponentAssets.delete(component)
        })

    deferInit(component.el, ready)
})

// Alpine.deferInit() ships in Alpine v3.17. If an older Alpine is bundled,
// degrade to loading the module without suspending the tree rather than
// breaking outright...
function deferInit(el, promise) {
    if (Alpine.deferInit) Alpine.deferInit(el, promise)
}

function modulePath(name, hash) {
    let encodedName = name.replace(/\./g, '--').replace(/::/g, '---').replace(/:/g, '----')

    return `${getModuleUrl()}/js/${encodedName}.js?v=${hash}`
}

// The morph gate answers "is this component's markup in the DOM yet?". With
// no in-flight request there's nothing to wait for: on the initial page load,
// a back/forward restore, or a child rendered by its parent's update, the
// markup is already present when the effect fires. For a message-driven
// effect (lazy hydration), the gate binds to the exact message that carried
// the effect — a concurrent message for the same component finishing first
// must not open it early...
let processingMessages = new WeakMap()

function morphGateFor(component, request) {
    if (! request) return Promise.resolve()

    let message = processingMessages.get(component)

    if (! message) return Promise.resolve()

    if (! morphGates.has(message)) {
        let resolve

        let promise = new Promise(r => resolve = r)

        morphGates.set(message, { promise, resolve })
    }

    return morphGates.get(message).promise
}

function releaseMorphGate(message) {
    let gate = morphGates.get(message)

    if (! gate) return

    morphGates.delete(message)

    gate.resolve()
}

interceptMessage(({ message, onSuccess, onCancel, onFailure, onError, onFinish }) => {
    onSuccess(({ onSync, onMorphed }) => {
        // Effects are processed between sync and morph — remember which
        // message is applying its response so the effect hook can bind the
        // gate to it...
        onSync(() => processingMessages.set(message.component, message))

        onMorphed(() => releaseMorphGate(message))
    })

    // However the message ends, its gate must open — a component would
    // otherwise stay suspended forever...
    onCancel(() => releaseMorphGate(message))
    onFailure(() => releaseMorphGate(message))
    onError(() => releaseMorphGate(message))
    onFinish(() => {
        if (processingMessages.get(message.component) === message) {
            processingMessages.delete(message.component)
        }

        releaseMorphGate(message)
    })
})

export function assetIsPendingFor(component) {
    return pendingComponentAssets.has(component) && pendingComponentAssets.get(component).loading
}

// True only when the component's tree was actually suspended — a pending
// module on an Alpine without deferInit() loads the old (racy) way, and the
// component initializes on the first pass like it always did...
export function treeIsSuspendedFor(component) {
    return !! Alpine.deferInit && assetIsPendingFor(component)
}

export function runAfterAssetIsLoadedFor(component, callback) {
    if (assetIsPendingFor(component)) {
        pendingComponentAssets.get(component).afterLoaded.push(() => callback())
    } else {
        callback()
    }
}
