import { closestComponent, findComponent } from '@/store'
import { on } from '@/events'
import { wireFallback, wireProperty } from '@/wire'
import { callMethod } from '@/request'

wireProperty('$set', (component) => (...params) => {
    return component.$wire.set(...params)
})

wireProperty('$toggle', (component) => (name) => {
    return component.$wire.set(name, ! component.$wire.get(name))
})
// Gotta put this somewhere else...
wireProperty('__target', (component) => component)
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
wireProperty('get', (component) => (property, reactive = true) => dataGet(reactive ? target.reactive : target.ephemeral, property))
wireProperty('set', (component) => async (property, value, live = true) => {
    dataSet(component.reactive, property, value)

    return live
        ? await requestCommit(component.symbol)
        : Promise.resolve()
})
wireProperty('call', (component) => (method, ...params) => {
    return component.$wire[method](...params)
})

wireFallback((component) => (property) => async (...params) => {
    // If this method is passed directly to a Vue or Alpine
    // event listener (@click="someMethod") without using
    // parens, strip out the automatically added event.
    if (params.length === 1 && params[0] instanceof Event) {
        params = []
    }

    return await callMethod(component.symbol, property, params)
})

wireProperty('$commit', (component) => async (callback) => {
    return await requestCommit(component.symbol)
})
