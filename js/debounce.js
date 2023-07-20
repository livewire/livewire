import { WeakBag } from './utils'

let callbacksByComponent = new WeakBag

export function debounceByComponent(component, callback, time) {
    // Prepare yourself for what's happening here.
    // Any text input with wire:model on it should be "debounced" by ~150ms by default.
    // We can't use a simple debounce function because we need a way to clear all the pending
    // debounces if a user submits a form or performs some other action.
    // This is a modified debounce function that acts just like a debounce, except it stores
    // the pending callbacks in a global property so we can "clear them" on command instead
    // of waiting for their setTimeouts to expire. I know.

    // This is a "null" callback. Each wire:model will resister one of these upon initialization.
    let callbackRegister = { callback: () => { } }
    callbacksByComponent.add(component, callbackRegister)

    // This is a normal "timeout" for a debounce function.
    var timeout

    return e => {
        clearTimeout(timeout)

        timeout = setTimeout(() => {
            callback(e)
            timeout = undefined

            // Because we just called the callback, let's return the
            // callback register to it's normal "null" state.
            callbackRegister.callback = () => { }
        }, time)

        // Register the current callback in the register as a kind-of "escape-hatch".
        callbackRegister.callback = () => {
            clearTimeout(timeout)
            callback(e)
        }
    }
}

export function callAndClearComponentDebounces(component, callback) {
    // This is to protect against the following scenario:
    // A user is typing into a debounced input, and hits the enter key.
    // If the enter key submits a form or something, the submission
    // will happen BEFORE the model input finishes syncing because
    // of the debounce. This makes sure to clear anything in the debounce queue.

    callbacksByComponent.each(component, callbackRegister => {
        callbackRegister.callback()
        callbackRegister.callback = () => { }
    })

    callback()
}
