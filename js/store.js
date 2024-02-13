import { Component } from "@/component";
import { trigger } from "@/hooks";

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

export function findComponent(id) {
    let component = components[id]

    if (! component) throw 'Component not found: ' + id

    return component
}

export function closestComponent(el, strict = true) {
    let closestRoot = Alpine.findClosest(el, i => i.__livewire)

    if (! closestRoot) {
        if (strict) throw "Could not find Livewire component in DOM tree"

        return
    }

    return closestRoot.__livewire
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
