import { trigger } from "@/hooks"
import { closestComponent } from "@/store"
import Alpine from 'alpinejs'

export function morph(component, el, html) {
    // console.log('morphstart', component.existingChildren)
    let wrapperTag = el.parentElement
        // If the root element is a "tr", we need the wrapper to be a "table"...
        ? el.parentElement.tagName.toLowerCase()
        : 'div'

    let wrapper = document.createElement(wrapperTag)

    wrapper.innerHTML = html
    let parentComponent

    try {
        parentComponent = closestComponent(el.parentElement)
    } catch (e) {}

    parentComponent && (wrapper.__livewire = parentComponent)

    let to = wrapper.firstElementChild

    to.__livewire = component

    trigger('morph', { el, toEl: to, component })

    // console.log('morph')
    // console.log('from', el.outerHTML)
    // console.log('to', to.outerHTML)
    // console.log('component', component)
    // console.log('componentChildren', component.existingChildren)

    let toChildComponents = to.querySelectorAll('[wire\\:id]')

    toChildComponents.forEach(child => {
        if (child.hasAttribute('wire:snapshot')) return
        
        // console.log('child', child.outerHTML)

        let existingComponent = document.querySelector(`[wire\\:id="${child.getAttribute('wire:id')}"]`)

        // console.log('existingComponent', existingComponent.outerHTML)

        child.replaceWith(existingComponent.cloneNode(true))
    })

    // console.log('fromAFTER', el.outerHTML)
    // console.log('toAFTER', to.outerHTML)

    // console.log('el', el)

    // Idea 1:
    // Walk the placeholders wire:id and empty (no wire:snapshot)
    // and then check the real DOM
    // if found match clone node true
    // and then do a .replaceWith() on the toEl

    // Idea 2:
    // QSAObserver package - replacement for custom web elements

    Alpine.morph(el, to, {
        updating: (el, toEl, childrenOnly, skip, skipChildren) => {
            if (isntElement(el)) return
            // console.log('updating')
            // console.log('from', el.outerHTML)
            // console.log('to', toEl.outerHTML)

            trigger('morph.updating', { el, toEl, component, skip, childrenOnly, skipChildren })

            // bypass DOM diffing for children by overwriting the content
            if (el.__livewire_replace === true) el.innerHTML = toEl.innerHTML;
            // completely bypass DOM diffing for this element and all children
            if (el.__livewire_replace_self === true) { el.outerHTML = toEl.outerHTML; return skip(); }

            if (el.__livewire_ignore === true) return skip()
            if (el.__livewire_ignore_self === true) childrenOnly()
            if (el.__livewire_ignore_children === true) return skipChildren()

            // Children will update themselves.
            // console.log('skippingComponent', isComponentRootEl(el) && el.getAttribute('wire:id') !== component.id, isComponentRootEl(el), el.outerHTML, el.getAttribute('wire\:id'), component.id)
            if (isComponentRootEl(el) && el.getAttribute('wire:id') !== component.id) return skip()

            // Give the root Livewire "to" element, the same object reference as the "from"
            // element. This ensures new Alpine magics like $wire and @entangle can
            // initialize in the context of a real Livewire component object.
            if (isComponentRootEl(el)) toEl.__livewire = component
        },

        updated: (el) => {
            if (isntElement(el)) return

            trigger('morph.updated', { el, component })
        },

        removing: (el, skip) => {
            if (isntElement(el)) return
            // console.log('removing', el.outerHTML)

            trigger('morph.removing', { el, component, skip })
        },

        removed: (el) => {
            if (isntElement(el)) return

            trigger('morph.removed', { el, component })
        },

        adding: (el) => {
            // console.log('adding', el.outerHTML)

            trigger('morph.adding', { el, component })
        },

        added: (el) => {
            if (isntElement(el)) return

            // console.log('added', el.outerHTML)

            const closestComponentId = closestComponent(el).id
            // console.log('added', el.outerHTML)

            trigger('morph.added', { el })
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

        lookahead: false,
    })

    trigger('morphed', { el, component })
}

function isntElement(el) {
    return typeof el.hasAttribute !== 'function'
}

function isComponentRootEl(el) {
    return el.hasAttribute('wire:id')
}
