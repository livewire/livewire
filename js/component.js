import { deepClone, deeplyEqual, extractData} from './utils'
import { store, processEffects } from './request'
import { generateWireObject } from './$wire'
import { findComponent } from './store';

export class Component {
    constructor(el) {
        if (el.__livewire) throw 'Component already initialized';

        el.__livewire = this

        this.symbol = Symbol()

        store.set(this.symbol, this)

        this.el = el

        this.id = el.getAttribute('wire:id')

        this.__livewireId = this.id // @legacy

        this.encodedSnapshot = el.getAttribute('wire:snapshot')

        this.snapshot = JSON.parse(this.encodedSnapshot)

        this.name = this.snapshot.memo.name

        this.effects = JSON.parse(el.getAttribute('wire:effects'))

        // "canonical" data represents the last known server state.
        this.canonical = extractData(deepClone(this.snapshot.data))
        // "ephemeral" represents the most current state. (This can be freely manipulated by end users)
        this.ephemeral = extractData(deepClone(this.snapshot.data))
        // "reactive" is just ephemeral, except when you mutate it, front-ends like Vue react.
        this.reactive = Alpine.reactive(this.ephemeral)

        // this.$wire = this.reactive
        this.$wire = generateWireObject(this, this.reactive)

        // Effects will be processed after every request, but we'll also handle them on initialization.
        processEffects(this, this.effects)
    }

    mergeNewSnapshot(encodedSnapshot, effects) {
        this.encodedSnapshot = encodedSnapshot

        let snapshot = JSON.parse(encodedSnapshot)

        this.snapshot = snapshot

        this.effects = effects

        this.canonical = extractData(deepClone(snapshot.data))

        let newData = extractData(deepClone(snapshot.data))

        Object.entries(this.ephemeral).forEach(([key, value]) => {
            if (! deeplyEqual(this.ephemeral[key], newData[key])) {
                this.reactive[key] = newData[key]
            }
        })
    }

    replayUpdate(snapshot, html, dirty) {
        let effects = { ...this.effects, html, dirty }

        this.mergeNewSnapshot(JSON.stringify(snapshot), effects)

        processEffects(this, { html, dirty })
    }

    get children() {
        let meta = this.snapshot.memo
        let childIds = Object.values(meta.children).map(i => i[1])

        return childIds.map(id => findComponent(id))
    }
}
