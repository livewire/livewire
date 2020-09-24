
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
        let result = true

        (this.listeners[name] || []).forEach(callback => {
            result = !! callback(...params)
        })

        return result
    }

    has(name) {
        return Object.keys(this.listeners).includes(name)
    }
}
