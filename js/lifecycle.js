import bootFeatures from './features'
import { findComponent, hasComponent, releaseComponent, resurrect, state, storeComponent } from './state'
import { synthetic, trigger } from './synthetic/index'
import { Component } from './component'
import Alpine from 'alpinejs'
import morph from '@alpinejs/morph'

export function start(options = {}) {
    let enabledFeatures = options.features || []

    bootFeatures(enabledFeatures)

    Alpine.interceptInit(Alpine.skipDuringClone(el => {
        initElement(el)
    }))

    Alpine.plugin(morph)

    Alpine.addRootSelector(() => '[wire\\:id]')

    Alpine.start()

    setTimeout(() => {
        window.Livewire.initialRenderIsFinished = true
    })
}

function initElement(el) {
    if (el.hasAttribute('wire:id')) {
        let id = el.getAttribute('wire:id')
        let initialData = JSON.parse(el.getAttribute('wire:initial-data'))

        if (! initialData) {
            initialData = resurrect(id)
        }

        let component = new Component(synthetic(initialData).__target, el, id)

        el.__livewire = component

        // This makes anything that would normally be available on $wire
        // available directly without needing to prefix "$wire.".
        Alpine.bind(el, {
            'x-data'() { return component.synthetic.reactive },
            // Disabling this for laracon...
            'x-destroy'() {
                releaseComponent(component.id)
            }
        })

        storeComponent(component.id, component)

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
