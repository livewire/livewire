
export function dispatch(eventName, { target, cancelable, data } = {}) {
    const event = document.createEvent("Events")
    event.initEvent(eventName, true, cancelable == true)
    event.data = data || {}

    // Fix setting `defaultPrevented` when `preventDefault()` is called
    // http://stackoverflow.com/questions/23349191/event-preventdefault-is-not-working-in-ie-11-for-custom-events
    if (event.cancelable && !preventDefaultSupported) {
        const { preventDefault } = event
        event.preventDefault = function () {
            if (!this.defaultPrevented) {
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

export function addMixin(classTarget, ...sources) {
    sources.forEach(source => {
        let descriptors = Object.keys(source).reduce((descriptors, key) => {
            descriptors[key] = Object.getOwnPropertyDescriptor(source, key);
            return descriptors;
        }, {});

        Object.getOwnPropertySymbols(source).forEach(sym => {
            let descriptor = Object.getOwnPropertyDescriptor(source, sym);
            if (descriptor.enumerable) {
                descriptors[sym] = descriptor;
            }
        });
        Object.defineProperties(classTarget.prototype, descriptors);
    });
    return classTarget.prototype;
}
