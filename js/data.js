import { on } from "./events";
import { dataGet, dataSet, decorate } from "./utils";

let defer = false
let preventRecurse = false

export function initializeReactiveDataCore(component) {
    let data = deepClone(component.canonicalData)
    let dataReactive = Alpine.reactive(data)

    // whenObjectIsMutated(dataReactive, (deepDiff) => {
        // if (preventRecurse) {
        //     preventRecurse = false
        //     return
        // }

        // Object.entries(deepDiff).forEach(([key, value]) => {
        //     addDataToPayload(component, key, value)
        //     defer || triggerRequest(component)
        // })
    // })

    on('component.response', (component, response) => {
        let data = component.canonicalData
        let transient = dataReactive.__v_raw

        Object.entries(data).forEach(([key, value]) => {
            if (JSON.stringify(data[key]) !== JSON.stringify(transient[key])) {
                preventRecurse = true
                component.dataReactive[key] = deepClone(data[key])
            }
        })
    })

    return [
        data,
        dataReactive
    ]
}

function deepClone(object) {
    return JSON.parse(JSON.stringify(object))
}

function whenObjectIsMutated(object, callback) {
    let firstTime = true

    let lastObject

    Alpine.effect(() => {
        if (firstTime) {
            recursivelyAccessAllObjectProperties(object)

            firstTime = false
        } else {
            callback(deepDiff(lastObject, object.__v_raw))
        }

        lastObject = deepClone(object)
    })
}

function recursivelyAccessAllObjectProperties(object) {
    let throwAway

    Object.entries(object).forEach(([key, value]) => {
        if (typeof value === 'object' && value !== null) {
            recursivelyAccessAllObjectProperties(value)
        }

        throwAway = object[key]
    })
}

function deepDiff(before, after) {
    let diff = {}

    Object.entries(after).forEach(([key, value]) => {
        if (JSON.stringify(after[key]) !== JSON.stringify(before[key])) {
            diff[key] = after[key]
        }
    })

    return diff
}

export function deferMutation(callback) {
    let holdover = defer

    defer = true

    callback()

    defer = holdover
}

export function generateWireObject(component) {
    return decorate(component.dataReactive, {
        entangle(name, defer = false) {
            return getEntangleFunction(component, name, defer)
        },

        get __instance() {
            return component
        },

        $refresh() {
            addActionToComponent(component, '$refresh', [property, value])

            triggerRequest(component)
        },

        $set(property, value) {
            addActionToComponent(component, '$set', [property, value])

            triggerRequest(component)
        },

        sync(property, value) {
            addDataToPayload(component, property, value)
            triggerRequest(component)
        },

        __get(property) {
            // This is a magic getter. If there is no property,
            // then this trap will get called. In these cases
            // we will assume the user wants to call a method
            // on the component who's name we don't know.
            let method = property

            return (...params) => {
                addActionToPayload(component, method, params)
                triggerRequest(component)
            }
        }
    })
}

function getEntangleFunction(component, name, defer) {
    let isDeferred = defer

    let livewireProperty = name
    let livewirePropertyValue = component.dataReactive[name]

    let livewireGetter = () => { return dataGet(component.dataReactive, name) }
    let livewireSetter = (value) => { dataSet(component.dataReactive, name, value) }

    let interceptor = Alpine.interceptor((initialValue, alpineGetter, alpineSetter, path, key) => {
        // Check to see if the Livewire property exists and if not log a console error
        // and return so everything else keeps running.
        if (typeof livewirePropertyValue === 'undefined') {
            console.error(`Livewire Entangle Error: Livewire property '${livewireProperty}' cannot be found`)
            return
        }

        // Let's set the initial value of the Alpine prop to the Livewire prop's value.
        let value = deepClone(livewirePropertyValue)

        queueMicrotask(() => {
            let firstTime1 = true
            Alpine.effect(() => {
                let value = alpineGetter()

                if (firstTime1) firstTime1 = false
                else {
                    if (isDeferred) {
                        deferMutation(() => {
                            livewireSetter(value)
                        })
                    } else {
                        livewireSetter(value)
                    }
                }
            })

            let firstTime2 = true
            Alpine.effect(() => {
                let value = livewireGetter()

                if (firstTime2) firstTime2 = false
                else alpineSetter(value)
            })
        })

        return value
    }, obj => {
        Object.defineProperty(obj, 'defer', {
            get() {
                isDeferred = true

                return obj
            }
        })
    })

    return interceptor(livewirePropertyValue)
}
