import { dataSet, deepClone, diff, extractData} from '@/utils'
import { generateWireObject } from '@/$wire'
import { closestComponent, findComponent } from '@/store'
import { trigger } from '@/hooks'

export class Component {
    constructor(el) {
        if (el.__livewire) throw 'Component already initialized';

        el.__livewire = this

        this.el = el

        this.id = el.getAttribute('wire:id')

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

        this.cleanups = []

        // Effects will be processed after every request, but we'll also handle them on initialization.
        this.processEffects(this.effects)
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

        Object.entries(dirty).forEach(([key, value]) => {
            let rootKey = key.split('.')[0]
            this.reactive[rootKey] = newData[rootKey]
        })
        // Object.entries(this.ephemeral).forEach(([key, value]) => {
        //     if (! deeplyEqual(this.ephemeral[key], newData[key])) {
        //         this.reactive[key] = newData[key]
        //     }
        // })

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
                if (diffKey.startsWith(updateValue)) {
                    delete diff[diffKey]
                }
            })

            diff[updateKey] = updateValue
        })

        this.queuedUpdates = []

        return diff
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
    processEffects(effects) {
        // This is for BC.
        trigger('effects', this, effects)

        trigger('effect', {
            component: this,
            effects,
            cleanup: i => this.addCleanup(i)
        })
    }

    get children() {
        let meta = this.snapshot.memo
        let childIds = Object.values(meta.children).map(i => i[1])

        return childIds.map(id => findComponent(id))
    }

    get parent() {
        return closestComponent(this.el.parentElement)
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
