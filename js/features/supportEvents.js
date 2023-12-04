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
        let handler = (e) => {
            if (e.__livewire) e.__livewire.receivedBy.push(component)

            component.$wire.call('__dispatch', name, e.detail || {})
        }

        window.addEventListener(name, handler)

        component.addCleanup(() => window.removeEventListener(name, handler))

        // Register a listener for when "to" or "self"
        component.el.addEventListener(name, (e) => {
            // We don't care about non-Livewire dispatches...
            if (! e.__livewire) return

            // We don't care about Livewire bubbling dispatches (only "to" and "self")...
            if (e.bubbles) return

            if (e.__livewire) e.__livewire.receivedBy.push(component.id)

            component.$wire.call('__dispatch', name, e.detail || {})
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

function dispatchEvent(target, name, params, bubbles = true) {
    let e = new CustomEvent(name, { bubbles, detail: params })

    e.__livewire = { name, params, receivedBy: [] }

    target.dispatchEvent(e)
}

export function dispatch(component, name, params) {
    dispatchEvent(component.el, name, params)
}

export function dispatchGlobal(name, params) {
    dispatchEvent(window, name, params)
}

export function dispatchSelf(component, name, params) {
    dispatchEvent(component.el, name, params, false)
}

export function dispatchTo(componentName, name, params) {
    let targets = componentsByName(componentName)

    targets.forEach(target => {
        dispatchEvent(target.el, name, params, false)
    })
}

export function listen(component, name, callback) {
    component.el.addEventListener(name, e => {
        callback(e.detail)
    })
}

export function on(eventName, callback) {
    let handler = (e) => {
        // Implemented for backwards compatibility...
        if (! e.__livewire) return

        callback(e.detail)
    }

    window.addEventListener(eventName, handler)

    return () => {
        window.removeEventListener(eventName, handler)
    }
}
