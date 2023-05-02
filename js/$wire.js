import { emit, emitSelf, emitTo, emitUp } from '@/features/supportDispatch'
import { generateEntangleFunction } from '@/features/supportEntangle'
import { closestComponent, findComponent } from '@/store'
import { callMethod, requestCommit } from '@/request'
import { dataGet, dataSet } from '@/utils'
import { on, trigger } from '@/events'
import Alpine from 'alpinejs'

let properties = {}
let fallback

function wireProperty(name, callback) {
    properties[name] = callback
}

function wireFallback(callback) {
    fallback = callback
}

export function generateWireObject(component, state) {
    return new Proxy({}, {
        get(target, property) {
            if (property === '__target') return component

            if (property in properties) {
                return getProperty(component, property)
            } else if (property in state) {
                return state[property]
            } else if (! ['then'].includes(property)) {
                return getFallback(component)(property)
            }
        },

        set(target, property, value) {
            if (property in state) {
                state[property] = value
            }
        },
    })
}

function getProperty(component, name) {
    return properties[name](component)
}

function getFallback(component) {
    return fallback(component)
}

Alpine.magic('wire', el => closestComponent(el).$wire)

wireProperty('__target', (component) => component)

wireProperty('get', (component) => (property, reactive = true) => dataGet(reactive ? component.reactive : component.ephemeral, property))

wireProperty('set', (component) => async (property, value, live = true) => {
    dataSet(component.reactive, property, value)

    return live
        ? await requestCommit(component.symbol)
        : Promise.resolve()
})

wireProperty('call', (component) => async (method, ...params) => {
    return await component.$wire[method](...params)
})

wireProperty('entangle', (component) => (name, live = false) => {
    return generateEntangleFunction(component)(name, live)
})

wireProperty('$set', (component) => (...params) => {
    return component.$wire.set(...params)
})

wireProperty('$toggle', (component) => (name) => {
    return component.$wire.set(name, ! component.$wire.get(name))
})

wireProperty('$watch', (component) => (path, callback) => {
    let firstTime = true
    let old = undefined

    effect(() => {
        let value = dataGet(component.reactive, path)

        if (firstTime) {
            firstTime = false
            return
        }

        pauseTracking()

        callback(value, old)

        old = value

        enableTracking()
    })

})

wireProperty('$watchEffect', (component) => (callback) => effect(callback))

wireProperty('$refresh', (component) => async () => await requestCommit(component.symbol))
wireProperty('$commit', (component) => async () => await requestCommit(component.symbol))

wireFallback((component) => (property) => async (...params) => {
    // If this method is passed directly to a Vue or Alpine
    // event listener (@click="someMethod") without using
    // parens, strip out the automatically added event.
    if (params.length === 1 && params[0] instanceof Event) {
        params = []
    }

    return await callMethod(component.symbol, property, params)
})

let parentMemo

wireProperty('$parent', component => {
    if (parentMemo) return parentMemo.$wire

    let parent = closestComponent(component.el.parentElement)

    parentMemo = parent

    return parent.$wire
})

wireProperty('$emit', (component) => (...params) => emit(...params))
wireProperty('$emitUp', (component) => (...params) => emitUp(component.el, ...params))
wireProperty('$emitSelf', (component) => (...params) => emitSelf(component.id, ...params))
wireProperty('$emitTo', (component) => (...params) => emitTo(...params))

wireProperty('emit', (component) => (...params) => emit(...params))
wireProperty('emitUp', (component) => (...params) => emitUp(component.el, ...params))
wireProperty('emitSelf', (component) => (...params) => emitSelf(component.id, ...params))
wireProperty('emitTo', (component) => (...params) => emitTo(...params))
