import { Component } from "@/component";
import { trigger } from "@/hooks";
import { deepClone } from "@/utils"
import componentException from '@/exceptions/componentException'

let components = {}

export function initComponent(el) {
    let component = new Component(el)

    if (components[component.id]) componentException(`Component ['${component.name}'] already registered`, el)

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

export function findComponent(id, el = null) {
    let component = components[id]

    if (! component && el) componentException(`Component ['${id}'] not found on ['${el.__livewire.name}']`, el)

    if (! component && !el) componentException(`Component ['${id}'] not found`)

    return component
}

export function closestComponent(el, strict = true) {
    let closestRoot = Alpine.findClosest(el, i => i.__livewire)

    if (! closestRoot) {
        if (strict) componentException('Could not find Livewire component in DOM tree', el)

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


