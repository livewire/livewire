import { registerSynth } from '@/synths'

/**
 * The rich client-side counterpart to PHP's Livewire\Selection. It extends
 * Array so Alpine's checkbox x-model treats it as a list and toggles key
 * membership natively, and so array-producing methods (concat/filter from
 * Alpine's checkbox handling) preserve the class through Symbol.species.
 */
export class Selection extends Array {
    all() { return [...this] }

    any() { return this.length > 0 }

    count() { return this.length }

    contains(key) {
        // Loose comparison — checkbox values are strings while server-side
        // keys are often integers...
        return this.some(i => i == key)
    }

    select(key) {
        if (! this.contains(key)) this.push(key)
    }

    deselect(key) {
        let index = this.findIndex(i => i == key)

        if (index !== -1) this.splice(index, 1)
    }

    toggle(key) {
        this.contains(key) ? this.deselect(key) : this.select(key)
    }

    clear() {
        this.splice(0, this.length)
    }
}

registerSynth('sel', {
    match: value => value instanceof Selection,

    hydrate: value => Selection.from(Array.isArray(value) ? value : []),

    dehydrate: value => [...value],
})
