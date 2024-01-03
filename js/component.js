import { dataSet, deepClone, deeplyEqual, diff, extractData} from '@/utils'
import { generateWireObject } from '@/$wire'
import { closestComponent, findComponent } from '@/store'
import { trigger } from '@/hooks'

export class Component {
    constructor(el) {
        let component = closestComponent(el, false)

        if (el.__livewire) {
            throw `Component ['${component.name}'] already initialized`
        }

        el.__livewire = this

        this.el = el

        this.id = el.getAttribute('wire:id')

        this.__livewireId = this.id // @legacy

        this.snapshotEncoded = el.getAttribute('wire:snapshot')

        this.snapshot = JSON.parse(this.snapshotEncoded)

        if (! this.snapshot) {
            throw {
                message: `Snapshot missing on Livewire component ['${component.name}'] with id: ['${this.id}']`,
                element: el
            };
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

        el.setAttribute('wire:effects', JSON.stringify(effects))
    }

    addCleanup(cleanup) {
        this.cleanups.push(cleanup)
    }

    cleanup() {
        while (this.cleanups.length > 0) {
            this.cleanups.pop()()
        }
    }
}
