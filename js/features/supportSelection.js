import { registerSynth } from '@/synths'

// Native checkboxes, plus Flux's ui-checkbox custom element — it honors
// the same .value/.checked/.indeterminate contracts...
function isCheckbox(el) {
    return el.tagName === 'UI-CHECKBOX'
        || (el.tagName === 'INPUT' && el.type === 'checkbox')
}

/**
 * The rich client-side counterpart to PHP's Livewire\Selection. Checkboxes
 * bind to it through the interceptWireModel contract below — each one models a
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
    // registered a value thunk in interceptWireModel()...
    selectPage() {
        this.__pageValues().forEach(value => this.select(value))
    }

    deselectPage() {
        this.__pageValues().forEach(value => this.deselect(value))
    }

    isPageSelected() {
        let values = this.__pageValues()

        return values.length > 0 && values.every(value => this.contains(value))
    }

    clear() {
        this.splice(0, this.length)
    }

    // A dedicated "select all" checkbox — the one at the top of a column —
    // binds to this facet: wire:model="selection.page". Checked models
    // "the whole page is selected", indeterminate marks a partial page,
    // and toggling selects or deselects every rendered row...
    get page() {
        let selection = this

        return {
            interceptWireModel(el, { effect }) {
                if (! isCheckbox(el)) return

                effect(() => {
                    let values = selection.__pageValues()
                    let selected = values.filter(value => selection.contains(value))

                    el.indeterminate = selected.length > 0 && selected.length < values.length
                })

                return {
                    get: () => selection.isPageSelected(),
                    set: checked => checked ? selection.selectPage() : selection.deselectPage(),
                }
            },
        }
    }

    // wire:model asks this value how an element should bind to it. A
    // checkbox with a value attribute models a boolean — "is my value
    // selected?" — and Alpine handles the rest. Anything else binds
    // like normal data...
    interceptWireModel(el, { cleanup }) {
        if (! isCheckbox(el) || ! el.hasAttribute('value')) return

        let value = () => el.isConnected ? el.value : undefined

        this.__registry().add(value)

        cleanup(() => this.__registry().delete(value))

        return {
            get: () => this.contains(el.value),
            set: checked => checked ? this.select(el.value) : this.deselect(el.value),
        }
    }

    __pageValues() {
        return [...this.__registry()]
            .map(value => value())
            .filter(value => value !== undefined && value !== null && value !== '')
    }

    // Value thunks live on the instance itself — merge() keeps the
    // instance alive across round-trips, so the set persists. Defined
    // non-enumerable so state walks (diff/dehydrate) never see it, but
    // writable/configurable so reactive proxies can wrap it freely...
    __registry() {
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
