import { componentsByName, findComponent } from '../store'
import { on as hook } from '@/events'
import { Bag, dispatch } from '@/utils'
import Alpine from 'alpinejs'
import { wireProperty } from '@/wire'

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

wireProperty('$emit', (component) => (...params) => emit(...params))
wireProperty('$emitUp', (component) => (...params) => emitUp(component.el, ...params))
wireProperty('$emitSelf', (component) => (...params) => emitSelf(component.id, ...params))
wireProperty('$emitTo', (component) => (...params) => emitTo(...params))

wireProperty('emit', (component) => (...params) => emit(...params))
wireProperty('emitUp', (component) => (...params) => emitUp(component.el, ...params))
wireProperty('emitSelf', (component) => (...params) => emitSelf(component.id, ...params))
wireProperty('emitTo', (component) => (...params) => emitTo(...params))

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
