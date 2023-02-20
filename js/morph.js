import { trigger } from './synthetic/index'
import { closestComponent } from "./lifecycle"
import Alpine from 'alpinejs'

export function morph(component, el, html) {
    let wrapper = document.createElement('div')

    wrapper.innerHTML = html
    let parentComponent

    try {
        parentComponent = closestComponent(el.parentElement)
    } catch (e) {}

    parentComponent && (wrapper.__livewire = parentComponent)

    let to = wrapper.firstElementChild

    to.__livewire = component

    trigger('morph', el, to, component)

    Alpine.morph(el, to, {
        updating: (el, toEl, childrenOnly, skip) => {
            if (isntElement(el)) return

            // trigger('element.updating', el, toEl, this)

            if (el.__livewire_ignore === true) return skip()
            if (el.__livewire_ignore_self === true) childrenOnly()

            // Children will update themselves.
            if (isComponentRootEl(el) && el.getAttribute('wire:id') !== component.id) return skip()

            // Give the root Livewire "to" element, the same object reference as the "from"
            // element. This ensures new Alpine magics like $wire and @entangle can
            // initialize in the context of a real Livewire component object.
            if (isComponentRootEl(el)) toEl.__livewire = component
        },

        updated: (el, toEl) => {
            if (isntElement(el)) return

            // trigger('element.updated', el, component)
        },

        removing: (el, skip) => {
            if (isntElement(el)) return

            trigger('morph.removing', el, skip)
        },

        removed: (el) => {
            if (isntElement(el)) return

            // trigger('element.removed', el, component)

            // if (el.__livewire) {
            //     store.removeComponent(el.__livewire)
            // }
        },

        adding: (el) => {
            trigger('morph.adding', el)
        },

        added: (el) => {
            if (isntElement(el)) return

            trigger('morph.added', el)

            const closestComponentId = closestComponent(el).id

            if (closestComponentId === component.id) {
                // @todo
                // if (nodeInitializer.initialize(el, component) === false) {
                //     return skip()
                // }
            } else if (isComponentRootEl(el)) {
                let data

                if (message.fingerprint && closestComponentId == message.fingerprint.id) {
                    data = {
                        fingerprint: message.fingerprint,
                        serverMemo: message.response.serverMemo,
                        effects: message.response.effects
                    }
                }

                // store.addComponent(new Component(el, this.connection, data))

                // We don't need to initialize children, the
                // new Component constructor will do that for us.
                el.skipAddingChildren = true
            }
        },

        key: (el) => {
            if (isntElement(el)) return

            return el.hasAttribute(`wire:key`)
                ? el.getAttribute(`wire:key`)
                : // If no "key", then first check for "wire:id", then "id"
                el.hasAttribute(`wire:id`)
                    ? el.getAttribute(`wire:id`)
                    : el.id
        },

        lookahead: true,
    })
}

function isntElement(el) {
    return typeof el.hasAttribute !== 'function'
}

function isComponentRootEl(el) {
    return el.hasAttribute('wire:id')
}
