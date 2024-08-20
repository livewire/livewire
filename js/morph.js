import { trigger } from "@/hooks"
import { closestComponent } from "@/store"
import Alpine from 'alpinejs'

export function morph(component, el, html) {
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


    let doMorph = () => {
        trigger('morph', { el, toEl: to, component })

        Alpine.morph(el, to, {
            updating: (el, toEl, childrenOnly, skip) => {
                if (el.localName === 'textarea' && el.closest('code-candy')) {
                    el.closest('code-candy').value = toEl.value
                    skip()
                   return
                }

                if (isntElement(el)) return

                trigger('morph.updating', { el, toEl, component, skip, childrenOnly })

                // bypass DOM diffing for children by overwriting the content
                if (el.__livewire_replace === true) el.innerHTML = toEl.innerHTML;
                // completely bypass DOM diffing for this element and all children
                if (el.__livewire_replace_self === true) { el.outerHTML = toEl.outerHTML; return skip(); }

                if (el.__livewire_ignore === true) return skip()
                if (el.__livewire_ignore_self === true) childrenOnly()

                // Children will update themselves.
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

                trigger('morph.removing', { el, component, skip })
            },

            removed: (el) => {
                if (isntElement(el)) return

                trigger('morph.removed', { el, component })
            },

            adding: (el) => {
                trigger('morph.adding', { el, component })
            },

            added: (el) => {
                if (isntElement(el)) return

                const closestComponentId = closestComponent(el).id

                trigger('morph.added', { el })
            },

            key: (el) => {
                if (el.hasAttribute('wire:unique')) return (Math.random() + 1).toString(36).substring(7);

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
}

    if (document.startViewTransition && el.querySelector('[wire\\:view-transition]')) {
        let undos = []

        Alpine.walk(el, (i, skip) => {
            if (i.nodeType !== Node.ELEMENT_NODE) return
            if (i.localName === 'svg') return skip()
            if (i.localName === 'ui-code') return skip()
            if (i.style.viewTransitionName !== '') return

            if (i.matches('[wire\\:view-transition]')) {
                i.style.viewTransitionName = (Math.random() + 1).toString(36).substring(7)

                undos.push(() => {
                    i.style.viewTransitionName = ''
                })
            } else if (i.closest('[wire\\:view-transition="deep"]')) {
                i.style.viewTransitionName = (Math.random() + 1).toString(36).substring(7)

                undos.push(() => {
                    i.style.viewTransitionName = ''
                })
            }
        })

        let transition = document.startViewTransition(() => {
            doMorph()
        })

        transition.finished.then(() => {
            undos.forEach(i => i())
        })
    } else {
        doMorph()
    }
}

function isntElement(el) {
    return typeof el.hasAttribute !== 'function'
}

function isComponentRootEl(el) {
    return el.hasAttribute('wire:id')
}

