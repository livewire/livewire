import { componentsByName } from "@/store"

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

function dispatchEvent(target, name, params, bubbles = true) {
    let e = new CustomEvent(name, { bubbles, detail: params })

    e.__livewire = { name, params, receivedBy: [] }

    target.dispatchEvent(e)
}
