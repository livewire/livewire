import { registerSynth } from '@/synths'

/**
 * The rich client-side counterpart to PHP's Livewire\Selection. Checkboxes
 * bind to it through the wireModel contract below — each one models a
 * boolean ("is my value selected?"), so Alpine's native checkbox handling
 * does all the element wiring and every interaction is an in-place
 * mutation. The synth's merge() keeps this instance's identity across
 * server round-trips...
 */
export class Selection extends Array {
    all() { return [...this] }

    any() { return this.length > 0 }

    isEmpty() { return ! this.any() }

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

    // The current "page" is whatever is rendered: every bound checkbox
    // registered a select callback in wireModel() — call them naively...
    selectPage() {
        this.__page().forEach(select => select())
    }

    clear() {
        this.splice(0, this.length)
    }

    // wire:model asks this value how an element should bind to it. A
    // checkbox with a value attribute models a boolean — "is my value
    // selected?" — and Alpine handles the rest. Anything else binds
    // like normal data...
    wireModel(el, { cleanup }) {
        if (el.type !== 'checkbox' || ! el.hasAttribute('value')) return

        let select = () => el.isConnected && this.select(el.value)

        this.__page().add(select)

        cleanup(() => this.__page().delete(select))

        return {
            get: () => this.contains(el.value),
            set: checked => checked ? this.select(el.value) : this.deselect(el.value),
        }
    }

    // Page callbacks live on the instance itself — merge() keeps the
    // instance alive across round-trips, so the set persists. Defined
    // non-enumerable so state walks (diff/dehydrate) never see it, but
    // writable/configurable so reactive proxies can wrap it freely...
    __page() {
        if (! this.__bindings) Object.defineProperty(this, '__bindings', { value: new Set(), writable: true, configurable: true })

        return this.__bindings
    }
}

registerSynth('sel', {
    match: value => value instanceof Selection,

    hydrate: value => Selection.from(Array.isArray(value) ? value : []),

    dehydrate: value => [...value],

    // Server-driven changes update the existing instance in place so its
    // identity — and the page callbacks living on it — survive round-trips...
    merge: (existing, incoming) => {
        existing.splice(0, existing.length, ...incoming)
    },
})
