import { cancelUpload, removeUpload, upload, uploadMultiple } from './features/supportFileUploads'
import { dispatch, dispatchSelf, dispatchTo, listen } from '@/events'
import { generateEntangleFunction } from '@/features/supportEntangle'
import { closestComponent } from '@/store'
import { requestCommit, requestCall } from '@/request'
import { dataGet, dataSet } from '@/utils'
import Alpine from 'alpinejs'

let properties = {}
let fallback

function wireProperty(name, callback, component = null) {
    properties[name] = callback
}

function wireFallback(callback) {
    fallback = callback
}

// For V2 backwards compatibility...
// And I actually like both depending on the scenario...
let aliases = {
    'on': '$on',
    'el': '$el',
    'id': '$id',
    'get': '$get',
    'set': '$set',
    'call': '$call',
    'commit': '$commit',
    'watch': '$watch',
    'entangle': '$entangle',
    'dispatch': '$dispatch',
    'dispatchTo': '$dispatchTo',
    'dispatchSelf': '$dispatchSelf',
    'upload': '$upload',
    'uploadMultiple': '$uploadMultiple',
    'removeUpload': '$removeUpload',
    'cancelUpload': '$cancelUpload',
}

export function generateWireObject(component, state) {
    return new Proxy({}, {
        get(target, property) {
            if (property === '__instance') return component

            if (property in aliases) {
                return getProperty(component, aliases[property])
            } else if (property in properties) {
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

            return true
        },
    })
}

function getProperty(component, name) {
    return properties[name](component)
}

function getFallback(component) {
    return fallback(component)
}

Alpine.magic('wire', (el, { cleanup }) => {
    // Purposely initializing an empty variable here is a "memo"
    // so that a component is lazy-loaded when using $wire from Alpine...
    let component

    // Override $wire methods that need to be cleaned up when
    // and element is removed. For example, `x-data="{ foo: $wire.entangle(...) }"`:
    // we would want the entangle effect freed if the element was removed from the DOM...
    return new Proxy({}, {
        get(target, property) {
            if (! component) component = closestComponent(el)

            if (['$entangle', 'entangle'].includes(property)) {
                return generateEntangleFunction(component, cleanup)
            }

            return component.$wire[property]
        },

        set(target, property, value) {
            if (! component) component = closestComponent(el)

            component.$wire[property] = value

            return true
        },
    })
})

wireProperty('__instance', (component) => component)

wireProperty('$get', (component) => (property, reactive = true) => dataGet(reactive ? component.reactive : component.ephemeral, property))

wireProperty('$el', (component) => {
    return component.el
})

wireProperty('$id', (component) => {
    return component.id
})

wireProperty('$set', (component) => async (property, value, live = true) => {
    dataSet(component.reactive, property, value)

    // If "live", send a request, queueing the property update to happen first
    // on the server, then trickle back down to the client and get merged...
    if (live) {
        component.queueUpdate(property, value)

        return await requestCommit(component)
    }

    return Promise.resolve()
})

wireProperty('$call', (component) => async (method, ...params) => {
    return await component.$wire[method](...params)
})

wireProperty('$entangle', (component) => (name, live = false) => {
    return generateEntangleFunction(component)(name, live)
})

wireProperty('$toggle', (component) => (name, live = true) => {
    return component.$wire.set(name, ! component.$wire.get(name), live)
})

wireProperty('$watch', (component) => (path, callback) => {
    let getter = () => {
        return dataGet(component.reactive, path)
    }

    let unwatch = Alpine.watch(getter, callback)

    component.addCleanup(unwatch)
})

wireProperty('$refresh', (component) => component.$wire.$commit)
wireProperty('$commit', (component) => async () => await requestCommit(component))

wireProperty('$on', (component) => (...params) => listen(component, ...params))

wireProperty('$dispatch', (component) => (...params) => dispatch(component, ...params))
wireProperty('$dispatchSelf', (component) => (...params) => dispatchSelf(component, ...params))
wireProperty('$dispatchTo', () => (...params) => dispatchTo(...params))
wireProperty('$upload', (component) => (...params) => upload(component, ...params))
wireProperty('$uploadMultiple', (component) => (...params) => uploadMultiple(component, ...params))
wireProperty('$removeUpload', (component) => (...params) => removeUpload(component, ...params))
wireProperty('$cancelUpload', (component) => (...params) => cancelUpload(component, ...params))

let parentMemo = new WeakMap

wireProperty('$parent', component => {
    if (parentMemo.has(component)) return parentMemo.get(component).$wire

    let parent = component.parent

    parentMemo.set(component, parent)

    return parent.$wire
})

let overriddenMethods = new WeakMap

export function overrideMethod(component, method, callback) {
    if (! overriddenMethods.has(component)) {
        overriddenMethods.set(component, {})
    }

    let obj = overriddenMethods.get(component)

    obj[method] = callback

    overriddenMethods.set(component, obj)
}

wireFallback((component) => (property) => async (...params) => {
    // If this method is passed directly to a Vue or Alpine
    // event listener (@click="someMethod") without using
    // parens, strip out the automatically added event.
    if (params.length === 1 && params[0] instanceof Event) {
        params = []
    }

    if (overriddenMethods.has(component)) {
        let overrides = overriddenMethods.get(component)

        if (typeof overrides[property] === 'function') {
            return overrides[property](params)
        }
    }

    return await requestCall(component, property, params)
})
