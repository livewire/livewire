import { trigger } from "./events"

let properties = {}
let fallback

export function wireProperty(name, callback) {
    properties[name] = callback
}

export function wireFallback(callback) {
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

