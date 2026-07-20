import { registerSynth } from '@/synths'
import { findComponent } from '@/store'
import Alpine from 'alpinejs'

// Every element bound to a selection, registered through the synth's bind
// contract: component -> path -> Set(el). The registry IS the current page —
// teleported elements stay covered because registration doesn't care where
// an element lives...
let bindings = new WeakMap()

function register(component, path, el, cleanup) {
    let byPath = bindings.get(component)

    if (! byPath) bindings.set(component, byPath = new Map())

    let els = byPath.get(path)

    if (! els) byPath.set(path, els = new Set())

    els.add(el)

    cleanup(() => els.delete(el))
}

/**
 * The rich client-side counterpart to PHP's Livewire\Selection. Checkbox
 * wiring goes through the synth's bind contract below — Alpine's array
 * x-model semantics never touch it, so every user interaction is an
 * in-place mutation and the instance is never replaced...
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

    // The current "page" is whatever is rendered: every element bound to
    // this selection, straight from the binding registry...
    selectPage() {
        for (let el of this.__boundEls()) {
            if (! el.isConnected) continue

            let value = elValue(el)

            if (value !== undefined && value !== null && value !== '') this.select(value)
        }
    }

    clear() {
        this.splice(0, this.length)
    }

    __boundEls() {
        let component = this.__componentId && findComponent(this.__componentId, false)

        if (! component) return []

        return bindings.get(component)?.get(this.__path) ?? []
    }

    // Remember which component and property this selection belongs to so
    // registry-aware methods like selectPage() can find their bound elements.
    // Non-enumerable so state walks (diff/dehydrate) never see them...
    __adopt(componentId, path) {
        Object.defineProperty(this, '__componentId', { value: componentId, enumerable: false, writable: true, configurable: true })
        Object.defineProperty(this, '__path', { value: path, enumerable: false, writable: true, configurable: true })

        return this
    }
}

function elValue(el) {
    return el.getAttribute('value') ?? el.value
}

registerSynth('sel', {
    match: value => value instanceof Selection,

    hydrate: (value, meta, context) => {
        let selection = Selection.from(Array.isArray(value) ? value : [])

        if (context) selection.__adopt(context.component.id, context.path)

        return selection
    },

    dehydrate: value => [...value],

    // Selections own their checkbox semantics: checked means "this value is
    // selected", toggling mutates the selection in place...
    bind({ el, component, path, get, notify, cleanup }) {
        let isCheckbox = el.tagName === 'INPUT' && el.type === 'checkbox' && el.hasAttribute('value')

        if (! isCheckbox) return false

        register(component, path, el, cleanup)

        Alpine.bind(el, {
            ['x-effect']() {
                el.checked = get().contains(elValue(el))
            },

            ['@change']() {
                get().toggle(elValue(el))

                notify()
            },
        })
    },
})
