import { componentsByName, findComponent } from '../state'
import { on as hook } from './../synthetic/index'
import Alpine from 'alpinejs'
import { Bag, dispatch } from 'utils'

let globalListeners = new Bag

export default function () {
    hook('effects', (target, effects, path) => {
        let listeners = effects.listeners
        if (! listeners) return

        listeners.forEach(name => {
            globalListeners.add(name, (...params) => {
                let component = findComponent(target.__livewireId)
                component.$wire.call('__emit', name, ...params)
            })

            queueMicrotask(() => {
                let component = findComponent(target.__livewireId)

                component.el.addEventListener('__lwevent:'+name, (e) => {
                    component.$wire.call('__emit', name, ...e.detail.params)
                })
            })
        })
    })

    hook('decorate', (target, path, addProp, decorator, symbol) => {
        addProp('$emit', (...params) => {
            emit(...params)
        })

        addProp('$emitUp', (...params) => {
            let component = findComponent(target.__livewireId)

            emitUp(component.el, ...params)
        })

        addProp('$emitSelf', (...params) => {
            let component = findComponent(target.__livewireId)

            emitSelf(component.id, ...params)
        })

        addProp('$emitTo', (...params) => {
            emitTo(...params)
        })
    })
}

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
