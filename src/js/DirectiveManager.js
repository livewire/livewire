import MessageBus from "./MessageBus"

export default {
    directives: new MessageBus,

    register(name, callback) {
        // If directive name already exists.
        // if (this.directives) {
        //     throw `Livewire: Referencing unknown hook: [${name}]`
        // }

        this.directives.register(name, callback)
    },

    call(name, el, directive, component) {
        this.directives.call(name, el, directive, component)
    },

    has(name) {
        return this.directives.has(name)
    },

    // call(name, ...params) {
    //     this.bus.call(name, ...params)
    // }
}
