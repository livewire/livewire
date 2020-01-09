
export default class MessageBus {
    constructor() {
        this.listeners = {}
    }

    register(name, callback) {
        if (! this.listeners[name]) {
            this.listeners[name] = []
        }

        this.listeners[name].push(callback)
    }

    call(name, ...params) {
        (this.listeners[name] || []).forEach(callback => {
            callback(...params)
        })
    }

    has(name) {
        return Object.keys(this.listeners).includes(name)
    }
}
