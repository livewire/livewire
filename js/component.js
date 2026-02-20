import { dataSet, deepClone, diff, diffAndConsolidate, diffAndPatchRecursive, extractData} from '@/utils'
import { generateWireObject } from '@/$wire'
import { findComponentByEl, findComponent, hasComponent } from '@/store'
import { trigger } from '@/hooks'
import { setNextActionOrigin } from '@/request'

export class Component {
    constructor(el) {
        if (el.__livewire) throw 'Component already initialized';

        el.__livewire = this

        this.el = el

        this.id = el.getAttribute('wire:id')

        this.key = el.getAttribute('wire:key')

        this.__livewireId = this.id // @legacy

        this.snapshotEncoded = el.getAttribute('wire:snapshot')

        this.snapshot = JSON.parse(this.snapshotEncoded)

        if (! this.snapshot) {
            throw `Snapshot missing on Livewire component with id: ` + this.id
        }

        this.name = this.snapshot.memo.name

        this.effects = JSON.parse(el.getAttribute('wire:effects'))
        this.originalEffects = deepClone(this.effects)

        // "canonical" data represents the last known server state.
        this.canonical = extractData(deepClone(this.snapshot.data))
        // "ephemeral" represents the most current state. (This can be freely manipulated by end users)
        this.ephemeral = extractData(deepClone(this.snapshot.data))
        // "reactive" is just ephemeral, except when you mutate it, front-ends like Vue react.
        this.reactive = Alpine.reactive(this.ephemeral)

        this.queuedUpdates = {}

        this.jsActions = {}

        // this.$wire = this.reactive
        this.$wire = generateWireObject(this, this.reactive)

        // Set the $wire property on the root element...
        el.$wire = this.$wire

        this.cleanups = []

        // Effects will be processed after every request, but we'll also handle them on initialization.
        this.processEffects(this.effects)
    }

    addActionContext(context) {
        // New system: just set the origin for next action
        if (context.el || context.directive) {
            setNextActionOrigin({
                el: context.el,
                directive: context.directive
            })
        }

        // Note: Non-origin metadata should be passed directly to fireAction
        // This method is kept for backwards compatibility but simplified
    }

    intercept(action, callback = null) {
        return this.$wire.$intercept(action, callback)
    }

    mergeNewSnapshot(snapshotEncoded, effects, updates = {}) {
        let snapshot = JSON.parse(snapshotEncoded)

        let oldCanonical = deepClone(this.canonical)
        let updatedOldCanonical = this.applyUpdates(oldCanonical, updates)

        let newCanonical = extractData(deepClone(snapshot.data))

        let dirty = diff(updatedOldCanonical, newCanonical)

        this.snapshotEncoded = snapshotEncoded

        this.snapshot = snapshot

        this.effects = effects

        this.canonical = extractData(deepClone(snapshot.data))

        let newData = extractData(deepClone(snapshot.data))

        // Diff old vs new and patch differences onto the reactive proxy. Walks
        // the trees directly, avoiding dot-notated paths which break when
        // object keys contain dots.
        diffAndPatchRecursive(updatedOldCanonical, newData, this.reactive)

        return dirty
    }

    queueUpdate(propertyName, value) {
        // These updates will be applied first on the server
        // on the next request, then trickle back to the
        // client on the next request that gets sent.
        this.queuedUpdates[propertyName] = value
    }

    mergeQueuedUpdates(diff) {
        // Before adding queuedUpdates into the diff list, we will remove any diffs
        // that will be overriden by the queued update. Queued updates will take
        // priority against ephemeral updates that have happend since them...
        Object.entries(this.queuedUpdates).forEach(([updateKey, updateValue]) => {
            Object.entries(diff).forEach(([diffKey, diffValue]) => {
                if (diffKey.startsWith(updateKey)) {
                    delete diff[diffKey]
                }
            })

            diff[updateKey] = updateValue
        })

        this.queuedUpdates = []

        return diff
    }

    getUpdates() {
        let propertiesDiff = diffAndConsolidate(this.canonical, this.ephemeral)

        return this.mergeQueuedUpdates(propertiesDiff)
    }

    applyUpdates(object, updates) {
        for (let key in updates) {
            dataSet(object, key, updates[key])
        }

        return object
    }

    replayUpdate(snapshot, html) {
        let effects = { ...this.effects, html}

        this.mergeNewSnapshot(JSON.stringify(snapshot), effects)

        this.processEffects({ html })
    }

    /**
     * Here we'll take the new state and side effects from the
     * server and use them to update the existing data that
     * users interact with, triggering reactive effects.
     */
    processEffects(effects, request) {
        // This is for BC.
        trigger('effects', this, effects)

        trigger('effect', {
            component: this,
            effects,
            cleanup: i => this.addCleanup(i),
            request,
        })
    }

    get children() {
        let componentEl = this.el

        let children = []

        componentEl.querySelectorAll('[wire\\:id]').forEach(el => {
            let parentComponentEl = el.parentElement.closest('[wire\\:id]')

            if (parentComponentEl !== componentEl) return

            let componentInstance = el.__livewire

            if (! componentInstance) return

            children.push(componentInstance)
        })

        return children
    }

    get islands() {
        let islands = this.snapshot.memo.islands

        return islands
    }

    get parent() {
        return findComponentByEl(this.el.parentElement)
    }

    get isIsolated() {
        return this.snapshot.memo.isolate
    }

    get isLazy() {
        return this.snapshot.memo.lazyLoaded !== undefined
    }

    get hasBeenLazyLoaded() {
        return this.snapshot.memo.lazyLoaded === true
    }

    get isLazyIsolated() {
        return !! this.snapshot.memo.lazyIsolated
    }

    getDeepChildrenWithBindings(callback) {
        this.getDeepChildren(child => {
            if (child.hasReactiveProps() || child.hasWireModelableBindings()) {
                callback(child)
            }
        })
    }

    hasReactiveProps() {
        let meta = this.snapshot.memo
        let props = meta.props

        return !! props
    }

    hasWireModelableBindings() {
        let meta = this.snapshot.memo
        let bindings = meta.bindings

        return !! bindings
    }

    getDeepChildren(callback) {
        this.children.forEach(child => {
            callback(child)

            child.getDeepChildren(callback)
        })
    }

    getEncodedSnapshotWithLatestChildrenMergedIn() {
        let { snapshotEncoded, children, snapshot } = this

        let childrenMemo = {}

        children.forEach(child => {
            childrenMemo[child.key] = [child.el.tagName.toLowerCase(), child.id]
        })

        return snapshotEncoded.replace(
            /"children":\{[^}]*\}/,
            `"children":${JSON.stringify(childrenMemo)}`
        )
    }

    inscribeSnapshotAndEffectsOnElement() {
        let el = this.el

        el.setAttribute('wire:snapshot', this.snapshotEncoded)

        // We need to re-register any event listeners that were originally registered...
        let effects = this.originalEffects.listeners
            ? { listeners: this.originalEffects.listeners }
            : {}

        // We need to re-register any url/query-string bindings...
        if (this.originalEffects.url) {
            effects.url = this.originalEffects.url
        }

        // We need to re-register any scripts that were originally registered...
        if (this.originalEffects.scripts) {
            effects.scripts = this.originalEffects.scripts;
        }

        el.setAttribute('wire:effects', JSON.stringify(effects))

        el.setAttribute('wire:key', this.key)
    }

    addJsAction(name, action) {
        this.jsActions[name] = action
    }

    hasJsAction(name) {
        return this.jsActions[name] !== undefined
    }

    getJsAction(name) {
        return this.jsActions[name].bind(this.$wire)
    }

    getJsActions() {
        return this.jsActions
    }

    // Called by JSON.stringify() on both $wire (via the Proxy) and the
    // Component instance directly. Without this, stringifying a Component
    // throws a circular reference error (el <-> component). Tools like
    // Laravel Boost trigger this when logging objects to the browser console.
    toJSON() {
        return {
            id: this.id,
            name: this.name,
            key: this.key,
            data: Object.fromEntries(Object.entries(this.ephemeral)),
        }
    }

    addCleanup(cleanup) {
        this.cleanups.push(cleanup)
    }

    cleanup() {
        delete this.el.__livewire

        while (this.cleanups.length > 0) {
            this.cleanups.pop()()
        }
    }
}
