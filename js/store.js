import { Component } from "@/component";
import { trigger } from "@/hooks";
import { walkBackwards, walkUpwards } from "./utils";
import { extractFragmentMetadataFromMarkerNode, isEndFragmentMarker, isStartFragmentMarker } from "./fragment";

let components = {}

export function initComponent(el) {
    let component = new Component(el)

    if (components[component.id]) throw 'Component already registered'

    let cleanup = (i) => component.addCleanup(i)

    trigger('component.init', { component, cleanup })

    components[component.id] = component

    return component
}

export function destroyComponent(id) {
    let component = components[id]

    if (! component) return

    component.cleanup()

    delete components[id]
}

export function hasComponent(id) {
    return !! components[id]
}

export function findComponent(id, strict = true) {
    let component = components[id]

    if (! component) {
        if (strict) throw 'Component not found: ' + id

        return
    }

    return component
}

export function findComponentByEl(el, strict = true) {
    let componentId = walkUpwards(el, (node, { stop }) => {
        if (node.__livewire) return stop(node.__livewire.id)

        let endMarkers = []

        let slotParentId = walkBackwards(node, (siblingNode, { stop }) => {
            if (isEndFragmentMarker(siblingNode)) {
                let metadata = extractFragmentMetadataFromMarkerNode(siblingNode)

                if (metadata.type !== 'slot') return

                endMarkers.push('a')

                return
            }

            if (isStartFragmentMarker(siblingNode)) {
                let metadata = extractFragmentMetadataFromMarkerNode(siblingNode)

                if (metadata.type !== 'slot') return


                if (endMarkers.length > 0) {
                    endMarkers.pop()
                } else {
                    return stop(metadata.parent)
                }
            }
        })

        if (slotParentId) return stop(slotParentId)
    })

    let component = findComponent(componentId, strict)

    if (! component) {
        if (strict) throw "Could not find Livewire component in DOM tree"

        return
    }

    return component
}

export function componentsByName(name) {
    return Object.values(components).filter(component => {
        return name == component.name
    })
}

export function getByName(name) {
    return componentsByName(name).map(i => i.$wire)
}

export function find(id) {
    let component = components[id]

    return component && component.$wire
}

export function first() {
    return Object.values(components)[0].$wire
}

export function all() {
    return Object.values(components)
}
