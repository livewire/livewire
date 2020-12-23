export function dispatch(eventName) {
    const event = document.createEvent('Events')

    event.initEvent(eventName, true, true)

    document.dispatchEvent(event)

    return event
}
