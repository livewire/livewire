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

    selectPage() {
        this.__pageValues().forEach(value => this.select(value))
    }

    clear() {
        this.splice(0, this.length)
    }

    // The current "page" is whatever is rendered: every element in this
    // selection's component that is wire:model'ed to it. The DOM is the
    // source of truth, so this works with any query, page, or filter...
    __pageValues() {
        let component = this.__component

        if (! component) return []

        let values = []

        for (let el of component.el.getElementsByTagName('*')) {
            for (let attr of el.attributes) {
                if (attr.name !== 'wire:model' && ! attr.name.startsWith('wire:model.')) continue

                if (attr.value !== this.__path) continue

                // Skip bindings that belong to a nested component...
                if (el.closest('[wire\\:id]') !== component.el) continue

                let value = el.getAttribute('value') ?? el.value

                if (value !== undefined && value !== null && value !== '') values.push(value)
            }
        }

        return values
    }

    // Remember which component and property this selection belongs to so
    // DOM-aware methods like selectPage() can find their bound elements.
    // Non-enumerable so state walks (diff/dehydrate) never see them...
    __adopt(component, path) {
        Object.defineProperty(this, '__component', { value: component, enumerable: false, writable: true, configurable: true })
        Object.defineProperty(this, '__path', { value: path, enumerable: false, writable: true, configurable: true })

        return this
    }

    // Alpine's checkbox x-model produces new instances through concat/filter.
    // Carry the component ref over so the replacement stays DOM-aware...
    concat(...args) {
        return super.concat(...args).__adopt(this.__component, this.__path)
    }

    filter(...args) {
        return super.filter(...args).__adopt(this.__component, this.__path)
    }
}

registerSynth('sel', {
    match: value => value instanceof Selection,

    hydrate: (value, meta, context) => {
        let selection = Selection.from(Array.isArray(value) ? value : [])

        if (context) selection.__adopt(context.component, context.path)

        return selection
    },

    dehydrate: value => [...value],
})
