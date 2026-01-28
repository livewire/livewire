import { trigger } from "@/hooks"
import { findComponentByEl } from "@/store"
import Alpine from 'alpinejs'
import { extractFragmentMetadataFromMarkerNode, isEndFragmentMarker, isStartFragmentMarker } from "./fragment"
import { transitionDomMutation } from "./directives/wire-transition"

export async function morph(component, el, html) {
    let wrapperTag = el.parentElement
        // If the root element is a "tr", we need the wrapper to be a "table"...
        ? el.parentElement.tagName.toLowerCase()
        : 'div'

    let customElement = customElements.get(wrapperTag)

    // If the wrapper tag is a custom element, we can't instantiate it using the hyphenated
    // tag name, so we need to get the name off the custom element instead...
    wrapperTag = customElement ? customElement.name : wrapperTag

    let wrapper = document.createElement(wrapperTag)

    wrapper.innerHTML = html
    let parentComponent

    try {
        parentComponent = findComponentByEl(el.parentElement)
    } catch (e) {}

    parentComponent && (wrapper.__livewire = parentComponent)

    let to = wrapper.firstElementChild

    // Set the snapshot and effects on the `to` element that way if there's a
    // mismatch or problem the component will able to be re-initialized...
    to.setAttribute('wire:snapshot', component.snapshotEncoded)

    // Remove the 'html' key from the effects as the html will be morphed...
    let effects = { ...component.effects }
    delete effects.html
    to.setAttribute('wire:effects', JSON.stringify(effects))

    to.__livewire = component

    trigger('morph', { el, toEl: to, component })

    // Let's first do a lookup of all the child components to see if the component already
    // exists and if so we'll clone it and replace the child component with the clone.
    // This is to ensure that components don't loose state even if there might be a
    // `wire:key` missing from elements within a loop around the component...
    let existingComponentsMap = {}

    el.querySelectorAll('[wire\\:id]').forEach(component => {
        existingComponentsMap[component.getAttribute('wire:id')] = component
    })

    to.querySelectorAll('[wire\\:id]').forEach(child => {
        // If the child has a `wire:snapshot` it means it's new, so we don't need to find it...
        if (child.hasAttribute('wire:snapshot')) return

        let wireId = child.getAttribute('wire:id')
        let existingComponent = existingComponentsMap[wireId]

        if (existingComponent) {
            child.replaceWith(existingComponent.cloneNode(true))
        }
    })

    let transitionOptions = component.effects.transition || {}

    await transitionDomMutation(el, to, () => {
        Alpine.morph(el, to, getMorphConfig(component))
    }, transitionOptions)

    trigger('morphed', { el, component })
}

export async function morphFragment(component, startNode, endNode, toHTML) {
    let fromContainer = startNode.parentElement
    let fromContainerTag = fromContainer ? fromContainer.tagName.toLowerCase() : 'div'

    let toContainer = document.createElement(fromContainerTag)
    toContainer.innerHTML = toHTML
    toContainer.__livewire = component

    // Add the parent component reference to an outer wrapper if it exists...
    let parentElement = component.el.parentElement
    let parentElementTag = parentElement ? parentElement.tagName.toLowerCase() : 'div'

    let parentComponent

    try {
        parentComponent = parentElement ? findComponentByEl(parentElement) : null
    } catch (e) {}

    if (parentComponent) {
        let parentProviderWrapper = document.createElement(parentElementTag)
        parentProviderWrapper.appendChild(toContainer)
        parentProviderWrapper.__livewire = parentComponent
    }

    trigger('island.morph', { startNode, endNode, component })

    let transitionOptions = component.effects.transition || {}

    await transitionDomMutation(fromContainer, toContainer, () => {
        Alpine.morphBetween(startNode, endNode, toContainer, getMorphConfig(component))
    }, transitionOptions)

    trigger('island.morphed', { startNode, endNode, component })
}

function getMorphConfig(component) {
    return {
        updating: (el, toEl, childrenOnly, skip, skipChildren, skipUntil) => {
            // Skip fragments...
            if (isStartFragmentMarker(el) && isStartFragmentMarker(toEl)) {
                let metadata = extractFragmentMetadataFromMarkerNode(toEl)

                if (metadata.mode !== 'morph') {
                    skipUntil(node => {
                        if (isEndFragmentMarker(node)) {
                            let endMarkerMetadata = extractFragmentMetadataFromMarkerNode(node)

                            return endMarkerMetadata.token === metadata.token
                        }

                        return false
                    })
                }
            }

            if (isntElement(el)) return

            trigger('morph.updating', { el, toEl, component, skip, childrenOnly, skipChildren, skipUntil })

            // bypass DOM diffing for children by overwriting the content
            if (el.__livewire_replace === true) el.innerHTML = toEl.innerHTML;
            // completely bypass DOM diffing for this element and all children
            if (el.__livewire_replace_self === true) { el.outerHTML = toEl.outerHTML; return skip(); }

            if (el.__livewire_ignore === true) return skip()
            if (el.__livewire_ignore_self === true) childrenOnly()
            if (el.__livewire_ignore_children === true) return skipChildren()

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

            const findComponentByElId = findComponentByEl(el).id

            trigger('morph.added', { el })
        },

        key: (el) => {
            if (isntElement(el)) return

            return el.hasAttribute(`wire:id`)
                ? el.getAttribute(`wire:id`)
                : // If no component "id", then first check for "wire:key", then "id"
                el.hasAttribute(`wire:key`)
                    ? el.getAttribute(`wire:key`)
                    : el.id
        },

        lookahead: false,
    }
}

function isntElement(el) {
    return typeof el.hasAttribute !== 'function'
}

function isComponentRootEl(el) {
    return el.hasAttribute('wire:id')
}
