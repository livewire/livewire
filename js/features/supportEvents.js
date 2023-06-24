import { componentsByName, findComponent } from '@/store'
import { on as hook, trigger } from '@/events'
import Alpine from 'alpinejs'

hook('effects', (component, effects) => {
    registerListeners(component, effects.listeners || [])

    dispatchEvents(component, effects.dispatches || [])
})

function registerListeners(component, listeners) {
    listeners.forEach(name => {
        // Register a global listener...
        window.addEventListener(name, (e) => {
            if (e.__livewire) e.__livewire.receivedBy.push(component)

            component.$wire.call('__dispatch', name, e.detail)
        })

        // Register a listener for when "to" or "self"
        component.el.addEventListener(name, (e) => {
            if (e.__livewire && e.bubbles) return

            if (e.__livewire) e.__livewire.receivedBy.push(component.id)

            component.$wire.call('__dispatch', name, e.detail)
        })
    })
}

function dispatchEvents(component, dispatches) {
    dispatches.forEach(({ name, params = {}, self = false, to }) => {
        if (self) dispatchSelf(component, name, params)
        else if (to) dispatchTo(component, to, name, params)
        else dispatch(component, name, params)
    })
}

function dispatchEvent(component, target, name, params, bubbles = true) {
    let e = new CustomEvent(name, { bubbles, detail: params })

    e.__livewire = { from: component.id, name, params, receivedBy: [] }

    trigger('dispatch', e)

    target.dispatchEvent(e)
}

export function dispatch(component, name, params) {
    dispatchEvent(component, window, name, params)
}

export function dispatchSelf(component, name, params) {
    dispatchEvent(component, component.el, name, params, false)
}

export function dispatchTo(component, componentName, name, params) {
    let targets = componentsByName(componentName)

    targets.forEach(target => {
        dispatchEvent(component, target.el, name, params, false)
    })
}

export function listen(component, name, callback) {
    component.el.addEventListener(name, e => {
        callback(e.detail)
    })
}

export function on() {
    // @todo: Implement for backwards compat...
}
