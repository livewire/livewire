import { componentsByName, findComponent } from '../store'
import { on as hook } from '@/events'
import { Bag, dispatch as dispatchEvent } from '@/utils'
import Alpine from 'alpinejs'

hook('effects', (component, effects) => {
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
            component.$wire.call('__dispatch', name, ...params)
        })

        queueMicrotask(() => {
            component.el.addEventListener('__lwevent:'+name, (e) => {
                component.$wire.call('__dispatch', name, ...e.detail.params)
            })
        })
    })
})

export function dispatch(name, ...params) {
    globalListeners.each(name, i => i(...params))
}

export function dispatchUp(el, name, ...params) {
    // todo: __lweevent? ew.
    dispatchEvent(el, '__lwevent:'+name, { params })
}

export function dispatchSelf(id, name, ...params) {
    let component = findComponent(id)

    dispatchEvent(component.el, '__lwevent:'+name, { params }, false)
}

export function dispatchTo(componentName, name, ...params) {
    let components = componentsByName(componentName)

    components.forEach(component => {
        dispatchEvent(component.el, '__lwevent:'+name, { params }, false)
    })
}

export function listen(component, name, callback) {
    component.el.addEventListener('__lwevent:'+name, e => {
        // @todo: Should we accept multiple parameters in sequence?
        let param = e.detail

        callback(e.detail)
    })
}

export function on(name, callback) {
    globalListeners.add(name, callback)
}
