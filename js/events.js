import { Bag } from "./utils"

let listeners = new Bag

export function on(name, callback) {
    listeners.add(name, callback)
}

export function trigger(name, ...params) {
    listeners.each(name, i => i(...params))
}

/**
 * Events: (@todo: organize these)
 *
 * -> element.init
 * -> component.initialized
 * -> component.request
 * -> component.response
 * -> request
 * -> message.sent
 * -> message.processed
 * -> message.failed
 */
