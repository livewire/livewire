import { registerSynth } from '@/synths'

// Native checkboxes, plus Flux's ui-checkbox/ui-switch custom elements —
// they honor the same .value/.checked/.indeterminate contracts, and
// Alpine's own isCheckbox() groups them the same way...
function isCheckbox(el) {
    return el.tagName === 'UI-CHECKBOX'
        || el.tagName === 'UI-SWITCH'
        || (el.tagName === 'INPUT' && el.type === 'checkbox')
}

/**
 * The rich client-side counterpart to PHP's Livewire\Selection. Checkboxes
 * bind to it through the interceptWireModel contract below — each one models a
 * boolean ("is my value selected?"), so Alpine's native checkbox handling
 * does all the element wiring and every interaction is an in-place
 * mutation. The synth's merge() keeps this instance's identity across
 * server round-trips.
 *
 * A selection is dual-mode: the array contents are the selected keys in
 * "include" mode, or the EXCEPTIONS in "except" mode (select-all across a
 * result set too big to enumerate). Every method routes through
 * contains()/select()/deselect(), so bound checkboxes and facets work
 * identically in both modes...
 */
export class Selection extends Array {
    keys() {
        if (this.isAll()) throw 'Livewire: [keys] is not available while a selection is in select-all mode — the selected keys cannot be enumerated. Check isAll() and use except() instead.'

        return [...this]
    }

    except() { return this.isAll() ? [...this] : [] }

    isAll() { return this.__mode === 'except' }

    isAllSelected() { return this.isAll() && this.length === 0 }

    any() { return this.isAll() || this.length > 0 }

    isEmpty() { return ! this.any() }

    // In except mode the count needs the total. The server feeds one via
    // setTotal() (it rides the snapshot meta), or pass one directly. Without
    // either, the count is unknowable: null...
    count(total = null) {
        if (! this.isAll()) return this.length

        total = total ?? this.__total ?? null

        return total === null ? null : Math.max(0, total - this.length)
    }

    total() { return this.__total ?? null }

    contains(key) {
        // Loose comparison — checkbox values are strings while server-side
        // keys are often integers...
        let has = this.some(i => i == key)

        return this.isAll() ? ! has : has
    }

    select(key) {
        this.isAll() ? this.__removeKey(key) : this.__addKey(key)
    }

    deselect(key) {
        this.isAll() ? this.__addKey(key) : this.__removeKey(key)
    }

    toggle(key) {
        this.contains(key) ? this.deselect(key) : this.select(key)
    }

    selectAll() {
        this.splice(0, this.length)

        this.__mode = 'except'
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

        this.__mode = 'include'
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

    __addKey(key) {
        if (! this.some(i => i == key)) this.push(key)
    }

    __removeKey(key) {
        let index = this.findIndex(i => i == key)

        if (index !== -1) this.splice(index, 1)
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

// Build a raw (pre-reactive) instance from wire format. __mode is defined
// non-enumerable HERE — before the reactive proxy wraps the instance — so
// later mode flips are plain (reactivity-triggering) assignments that
// keep the descriptor...
function fromWire(value, meta) {
    // A plain list means include mode...
    let isList = Array.isArray(value)

    let keys = isList ? value : (Array.isArray(value?.keys) ? value.keys : [])

    let selection = Selection.from(keys)

    Object.defineProperty(selection, '__mode', {
        value: (! isList && value?.mode === 'except') ? 'except' : 'include',
        writable: true,
        configurable: true,
    })

    // The total only ever arrives through server-owned meta...
    Object.defineProperty(selection, '__total', {
        value: meta?.total ?? null,
        writable: true,
        configurable: true,
    })

    return selection
}

registerSynth('sel', {
    match: value => value instanceof Selection,

    hydrate: (value, meta) => fromWire(value, meta),

    dehydrate: value => ({
        mode: value.isAll() ? 'except' : 'include',
        keys: [...value],
    }),

    // Server-driven changes update the existing instance in place so its
    // identity — and the page callbacks living on it — survive round-trips...
    merge: (existing, incoming) => {
        existing.splice(0, existing.length, ...incoming)

        existing.__mode = incoming.isAll() ? 'except' : 'include'
        existing.__total = incoming.__total ?? null
    },
})
