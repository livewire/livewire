import { isFunction } from "./utils"

/**
 * Dispatch a custom browser event...
 */
export function dispatch(event, payload) {
    document.dispatchEvent(new CustomEvent('synthetic:'+event, { detail: payload }))
}

/**
 * Our internal event listener bus...
 */
let listeners = []

/**
 * Register a callback to run when an event is triggered...
 */
export function on(name, callback) {
    if (! listeners[name]) listeners[name] = []

    listeners[name].push(callback)
}

/**
 * In addition to triggering an event, this method allows you to
 * defer running callbacks returned from listeners and pass a
 * value through each one so they can act like middleware.
 *
 * An example of using this combination to the fullest:
 *
 * // First let's look at the triggering phase:
 * let finish = trigger([event name], ...[event params])
 *
 * return finish([pass-through value])
 *
 * // Now, let's look at the "listening" phase:
 * on([event name], (...[event params]) => {
 *     // The contents of this callback will be run immediately on trigger.
 *
 *     return ([pass-through value]) => {
 *         // This callback will be run when "finish()" is called.
 *
 *         // The [pass-through value] can be mutated and must
 *         // be returned for the next callback to process.
 *         return [pass-through value]
 *     }
 * })
 */
export function trigger(name, ...params) {
    let callbacks = listeners[name] || []

    let finishers = []

    for (let i = 0; i < callbacks.length; i++) {
        let finisher = callbacks[i](...params)

        if (isFunction(finisher)) finishers.push(finisher)
    }

    return (result) => {
        let latest = result

        for (let i = 0; i < finishers.length; i++) {
            latest = finishers[i](latest)
        }

        return latest
    }
}
