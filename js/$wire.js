import { cancelUpload, removeUpload, upload, uploadMultiple } from './features/supportFileUploads'
import { dispatch, dispatchSelf, dispatchTo, dispatchRef, listen } from '@/events'
import { generateEntangleFunction } from '@/features/supportEntangle'
import { closestComponent } from '@/store'
import { requestCommit, requestCall } from '@/request'
import { dataGet, dataSet } from '@/utils'
import Alpine from 'alpinejs'
import { on as hook } from './hooks'
import requestBus from './v4/requests/requestBus'
import messageBroker from './v4/requests/messageBroker'
import { getErrorsObject } from './v4/features/supportErrors'
import { getPaginatorObject } from './v4/features/supportPaginators'
import interceptorRegistry from './v4/interceptors/interceptorRegistry'
import { findRef } from './v4/features/supportRefs'

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
    'js': '$js',
    'get': '$get',
    'set': '$set',
    'ref': '$ref',
    // Alias `refs` to `$ref` so it matches Alpine...
    'refs': '$ref',
    // Alias `$refs` to `$ref` so it matches Alpine...
    '$refs': '$ref',
    'call': '$call',
    'hook': '$hook',
    'watch': '$watch',
    'commit': '$commit',
    'errors': '$errors',
    'upload': '$upload',
    'entangle': '$entangle',
    'dispatch': '$dispatch',
    'intercept': '$intercept',
    'paginator': '$paginator',
    'dispatchTo': '$dispatchTo',
    'dispatchRef': '$dispatchRef',
    'dispatchSelf': '$dispatchSelf',
    'removeUpload': '$removeUpload',
    'cancelUpload': '$cancelUpload',
    'uploadMultiple': '$uploadMultiple',
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

wireProperty('$js', (component) => {
    let fn = component.addJsAction.bind(component)

    let jsActions = component.getJsActions()

    Object.keys(jsActions).forEach((name) => {
        fn[name] = component.getJsAction(name)
    })

    return fn
})

wireProperty('$set', (component) => async (property, value, live = true) => {
    dataSet(component.reactive, property, value)

    // If "live", send a request, queueing the property update to happen first
    // on the server, then trickle back down to the client and get merged...
    if (live) {
        if (requestBus.booted) {
            component.queueUpdate(property, value)

            return messageBroker.addAction(component, '$set')
        }

        component.queueUpdate(property, value)

        return await requestCommit(component)
    }

    return Promise.resolve()
})

wireProperty('$ref', (component) => {
    let fn = (name) => findRef(component, name)

    return new Proxy(fn, {
        get(target, property) {
            if (property in target) {
                return target[property]
            }
            return fn(property)
        }
    })
})

wireProperty('$intercept', (component) => (action, callback = null) => {
    if (callback === null) {
        callback = action
        action = null
    }

    interceptorRegistry.add(callback, component, action)
})

wireProperty('$errors', (component) => getErrorsObject(component))

wireProperty('$paginator', (component) => {
    let fn = (name = 'page') => getPaginatorObject(component, name)

    let defaultPaginator = fn()

    for (let key of Object.keys(defaultPaginator)) {
        let value = defaultPaginator[key]

        if (typeof value === 'function') {
            fn[key] = (...args) => defaultPaginator[key](...args)
        } else {
            Object.defineProperty(fn, key, {
                get: () => defaultPaginator[key],
                set: val => { defaultPaginator[key] = val },
            })
        }
    }

    return fn
})

wireProperty('$call', (component) => async (method, ...params) => {
    return await component.$wire[method](...params)
})

wireProperty('$island', (component) => async (name) => {
    messageBroker.addContext(component, 'islands', name)

    return await component.$wire.$refresh()
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

wireProperty('$refresh', (component) => async () => {
    if (requestBus.booted) {
        return messageBroker.addAction(component, '$refresh')
    }

    return component.$wire.$commit()
})
wireProperty('$commit', (component) => async () => {
    if (requestBus.booted) {
        return messageBroker.addAction(component, '$sync')
    }

    return await requestCommit(component)
})

wireProperty('$on', (component) => (...params) => listen(component, ...params))

wireProperty('$hook', (component) => (name, callback) => {
    let unhook = hook(name, ({component: hookComponent, ...params}) => {
        // Request level hooks don't have a component, so just run the callback
        if (hookComponent === undefined) return callback(params)

        // Run the callback if the component in the hook matches the $wire component
        if (hookComponent.id === component.id) return callback({component: hookComponent, ...params})
    })

    component.addCleanup(unhook)

    // Return the unhook function so it can be called manually if needed
    return unhook
})

wireProperty('$dispatch', (component) => (...params) => dispatch(component, ...params))
wireProperty('$dispatchSelf', (component) => (...params) => dispatchSelf(component, ...params))
wireProperty('$dispatchTo', () => (...params) => dispatchTo(...params))
wireProperty('$dispatchRef', (component) => (...params) => dispatchRef(component, ...params))
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

    if (requestBus.booted) {
        return messageBroker.addAction(component, property, params)
    }

    return await requestCall(component, property, params)
})
