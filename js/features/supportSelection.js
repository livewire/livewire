import { registerSynth } from '@/synths'
import { findComponent } from '@/store'
import Alpine from 'alpinejs'

// Every binding attached to a selection, registered through the bindTo
// contract: component -> path -> Set(binding). Each binding knows how to
// read its element's value — the registry IS the current page, and it's
// queryable (unlike events), which is what page-level affordances like
// selectPage() and a future select-all facet need...
let registry = new WeakMap()

function register(component, path, binding, cleanup) {
    let byPath = registry.get(component)

    if (! byPath) registry.set(component, byPath = new Map())

    let bindings = byPath.get(path)

    if (! bindings) byPath.set(path, bindings = new Set())

    bindings.add(binding)

    cleanup(() => bindings.delete(binding))
}

/**
 * The rich client-side counterpart to PHP's Livewire\Selection. Checkbox
 * wiring goes through the bindTo contract below — Alpine's array x-model
 * semantics never touch it, so every user interaction is an in-place
 * mutation — and the synth's merge() keeps this instance's identity
 * across server-driven changes...
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

    // The current "page" is whatever is rendered: every binding attached
    // to this selection, each asked for its element's value...
    selectPage() {
        for (let binding of this.__bindings()) {
            let value = binding.value()

            if (value !== undefined && value !== null && value !== '') this.select(value)
        }
    }

    clear() {
        this.splice(0, this.length)
    }

    // Selections own their element semantics: checked means "this value is
    // selected", toggling mutates the selection in place. Reads go through
    // binding.get() — never `this` — so the binding keeps working even if
    // this instance is ever replaced...
    bindTo({ el, component, path, get, notify, cleanup }) {
        let isCheckbox = el.tagName === 'INPUT' && el.type === 'checkbox' && el.hasAttribute('value')

        if (! isCheckbox) return false

        let value = () => el.isConnected ? (el.getAttribute('value') ?? el.value) : undefined

        register(component, path, { el, value }, cleanup)

        Alpine.bind(el, {
            ['x-effect']() {
                el.checked = get().contains(value())
            },

            ['@change']() {
                get().toggle(value())

                notify()
            },
        })
    }

    __bindings() {
        let component = this.__componentId && findComponent(this.__componentId, false)

        if (! component) return []

        return registry.get(component)?.get(this.__path) ?? []
    }

    // Remember which component and property this selection belongs to so
    // registry-aware methods like selectPage() can find their bindings.
    // Non-enumerable so state walks (diff/dehydrate) never see them...
    __adopt(componentId, path) {
        Object.defineProperty(this, '__componentId', { value: componentId, enumerable: false, writable: true, configurable: true })
        Object.defineProperty(this, '__path', { value: path, enumerable: false, writable: true, configurable: true })

        return this
    }
}

registerSynth('sel', {
    match: value => value instanceof Selection,

    hydrate: (value, meta, context) => {
        let selection = Selection.from(Array.isArray(value) ? value : [])

        if (context) selection.__adopt(context.component.id, context.path)

        return selection
    },

    dehydrate: value => [...value],

    // Server-driven changes update the existing instance in place so its
    // identity (and adopted context) survive the round-trip...
    merge: (existing, incoming) => {
        existing.splice(0, existing.length, ...incoming)
    },
})
