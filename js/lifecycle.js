import bootFeatures from './features'
import { state } from './state'
import { trigger } from './events'
import { synthetic } from './../../synthetic/js/index'
import { Component } from './component'

export function start() {
    bootFeatures()

    Alpine.interceptInit(Alpine.skipDuringClone(el => {
        initElement(el)
    }))
}

function initElement(el) {
    if (el.hasAttribute('wire:id')) {
        let id = el.getAttribute('wire:id')
        let raw = JSON.parse(el.getAttribute('wire:initial-data'))

        let component = new Component(synthetic(raw).__target, el, id)

        el.__livewire = component

        // This makes anything that would normally be available on $wire
        // available directly without needing to prefix "$wire.".
        Alpine.bind(el, {
            'x-data'() { return component.synthetic.reactive }
        })

        state.components[component.id] = component

        trigger('component.initialized', component)
    }

    let component

    // @todo: This is bad flow.
    // We have this in a try / catch, becuase if you try to find the "closest component"
    // and one if not found, it will error out rather than breaking things
    // downstream, but in this case we don't want to error out.
    try { component = closestComponent(el) } catch (e) {}

    component && trigger('element.init', el, component)
}

export function closestComponent(el) {
    let closestRoot = Alpine.findClosest(el, i => i.__livewire)

    if (! closestRoot) {
        throw "Could not find Livewire component in DOM tree"
    }

    return closestRoot.__livewire
}
