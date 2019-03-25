// I grabbed this from Turbolink's codebase.
export function dispatch(eventName, { target, cancelable, data } = {}) {
    const event = document.createEvent("Events")
    event.initEvent(eventName, true, cancelable == true)
    event.data = data || {}

    // Fix setting `defaultPrevented` when `preventDefault()` is called
    // http://stackoverflow.com/questions/23349191/event-preventdefault-is-not-working-in-ie-11-for-custom-events
    if (event.cancelable && ! preventDefaultSupported) {
        const { preventDefault } = event
        event.preventDefault = function () {
            if (! this.defaultPrevented) {
                Object.defineProperty(this, "defaultPrevented", { get: () => true })
            }
            preventDefault.call(this)
        }
    }

    (target || document).dispatchEvent(event)
    return event
}

const preventDefaultSupported = (() => {
    const event = document.createEvent("Events")
    event.initEvent("test", true, true)
    event.preventDefault()
    return event.defaultPrevented
})()
