import { dispatchRef } from "@/events"

export function findRef(component, name) {
    let refEl = component.el.querySelector(`[wire\\:ref="${name}"]`)

    if (! refEl) return console.error(`Ref "${name}" not found in component "${component.id}"`)

    let $wire = refEl.__livewire?.$wire

    return new Proxy({
        el: refEl,
        dispatch(eventName, params) {
            dispatchRef(component, name, eventName, params)
        },
    }, {
        get(target, property) {
            if (property in target) return target[property]

            if (! $wire) return console.error(`Ref "${name}" is not a component`)

            return $wire[property]
        },
        set(target, property, value) {
            if (! $wire) return console.error(`Ref "${name}" is not a component`)

            $wire[property] = value

            return true
        },
    })
}

export function findRefEl(component, name) {
    return findRef(component, name).el
}
