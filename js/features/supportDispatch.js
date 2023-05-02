import { componentsByName, findComponent } from '../store'
import { on as hook } from '@/events'
import { Bag, dispatch } from '@/utils'
import Alpine from 'alpinejs'

// support dispatchBrowserEvents
on('effects', (component, effects) => {
    let dispatches = effects.dispatches
    if (! dispatches) return

    dispatches.forEach(({ event, data }) => {
        data = data || {}

        let e = new CustomEvent(event, {
            bubbles: true,
            detail: data,
        })

        component.el.dispatchEvent(e)
    })
})

let globalListeners = new Bag

hook('effects', (component, effects, path) => {
    let listeners = effects.listeners

    if (! listeners) return

    listeners.forEach(name => {
        globalListeners.add(name, (...params) => {
            component.$wire.call('__emit', name, ...params)
        })

        queueMicrotask(() => {
            component.el.addEventListener('__lwevent:'+name, (e) => {
                component.$wire.call('__emit', name, ...e.detail.params)
            })
        })
    })
})

export function emit(name, ...params) {
    globalListeners.each(name, i => i(...params))
}

export function emitUp(el, name, ...params) {
    dispatch(el, '__lwevent:'+name, { params })
}

export function emitSelf(id, name, ...params) {
    let component = findComponent(id)

    dispatch(component.el, '__lwevent:'+name, { params }, false)
}

export function emitTo(componentName, name, ...params) {
    let components = componentsByName(componentName)

    components.forEach(component => {
        dispatch(component.el, '__lwevent:'+name, { params }, false)
    })
}

export function on(name, callback) {
    globalListeners.add(name, callback)
}
