import MessageBus from "./MessageBus"

export default {
    directives: new MessageBus,

    register(name, callback) {
        if (this.has(name)) {
            throw `Livewire: Directive already registered: [${name}]`
        }

        this.directives.register(name, callback)
    },

    call(name, el, directive, component) {
        this.directives.call(name, el, directive, component)
    },

    has(name) {
        return this.directives.has(name)
    },
}
