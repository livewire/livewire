import { componentsByName, findComponent } from '@/store'
import { on as hook } from '@/events'
import Alpine from 'alpinejs'

hook('effects', (component, effects) => {
    registerListeners(component, effects.listeners || [])

    dispatchEvents(component, effects.dispatches || [])
})

function registerListeners(component, listeners) {
    listeners.forEach(name => {
        // Register a global listener...
        window.addEventListener(name, (e) => {
            component.$wire.call('__dispatch', name, e.detail)
        })

        // Register a listener for when "to" or "self"
        component.el.addEventListener(name, (e) => {
            if (e.__livewire && e.bubbles) return

            component.$wire.call('__dispatch', name, e.detail)
        })
    })
}

function dispatchEvents(component, dispatches) {
    dispatches.forEach(({ name, params = {}, self = false, to }) => {
        if (self) dispatchSelf(component.id, name, params)
        else if (to) dispatchTo(to, name, params)
        else dispatch(name, params)
    })
}

function dispatchEvent(el, name, params, bubbles = true) {
    let e = new CustomEvent(name, { bubbles, detail: params })

    e.__livewire = { name, params }

    el.dispatchEvent(e)
}

export function dispatch(name, params) {
    dispatchEvent(window, name, params)
}

export function dispatchSelf(id, name, params) {
    let component = findComponent(id)

    dispatchEvent(component.el, name, params, false)
}

export function dispatchTo(componentName, name, params) {
    let components = componentsByName(componentName)

    components.forEach(component => {
        dispatchEvent(component.el, name, params, false)
    })
}

export function listen(component, name, callback) {
    component.el.addEventListener(name, e => {
        // @todo: Should we accept multiple parameters in sequence?


        callback(e.detail)
    })
}

export function on(name, callback) {
    globalListeners.add(name, callback)
}
