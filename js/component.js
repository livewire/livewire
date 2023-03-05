import { store, reactive, processEffects, extractDataAndDecorate, extractData, trigger } from './synthetic/index'
import { deepClone } from './synthetic/utils'

export class Component {
    constructor(el) {
        this.id = el.getAttribute('wire:id')
        this.effects = JSON.parse(el.getAttribute('wire:effects'))
        this.encodedSnapshot = el.getAttribute('wire:snapshot')
        this.snapshot = JSON.parse(this.encodedSnapshot)

        let symbol = Symbol()
        store.set(symbol, this)

        // "canonical" data represents the last known server state.
        this.canonical = extractData(deepClone(this.snapshot.data), symbol)
        // "ephemeral" represents the most current state. (This can be freely manipulated by end users)
        this.ephemeral = extractDataAndDecorate(deepClone(this.snapshot.data), symbol)

        // "reactive" is just ephemeral, except when you mutate it, front-ends like Vue react.
        this.reactive = reactive(this.ephemeral)

        trigger('new', this)

        // Effects will be processed after every request, but we'll also handle them on initialization.
        processEffects(this)

        this.synthetic = this
        this.$wire = this.reactive
        this.el = el
        this.name = this.snapshot.memo.name

        // So we can get Livewire components back from synthetic hooks.
        this.__livewireId = this.id
    }
}
