import { closestComponent } from "../lifecycle";
import { state } from "../state";
import { on } from './../../../synthetic/js/index'

export default function () {
    // This doesn't belong here, it bundles children with parents for the request...
    on('request.before', (target) => {
        let childIds = Object.values(target.snapshot.data[1].children).map(i => i[1])

        childIds.forEach((id) => {
            state.components[id].synthetic.ephemeral.$commit()
        })
    })

    on('effects', (target, effects, path) => {
        let component = state.components[target.__livewireId]

        let html = effects.html
        if (! html) return

        doMorph(component, component.el, html)
    })
}

function createElement(html) {
    const template = document.createElement('template')
    template.innerHTML = html
    return template.content.firstElementChild
}

function doMorph(component, el, html) {
    let to = createElement(html)

    to.__livewire = component

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
        },

        removed: (el) => {
            if (isntElement(el)) return

            // trigger('element.removed', el, component)

            // if (el.__livewire) {
            //     store.removeComponent(el.__livewire)
            // }
        },


        added: (el) => {
            if (isntElement(el)) return

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
