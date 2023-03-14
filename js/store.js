import { Component } from "./component";
import { trigger } from "./events";
import { deepClone } from "./utils"

let components = {}

export function initComponent(el) {
    let component = new Component(el)

    if (components[component.id]) throw 'Component already registered'

    trigger('component.init', component)

    components[component.id] = component
}

export function hasComponent(id) {
    return !! components[id]
}

export function findComponent(id) {
    let component = components[id]

    if (! component) throw 'Component not found: '.id

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

export function find(id) {
    let component = components[id]

    return component && component.$wire
}

export function first() {
    return Object.values(components)[0].$wire
}

