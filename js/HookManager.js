import MessageBus from "./MessageBus"

export default {
    availableHooks: [
        'componentInitialized',
        'elementInitialized',
        'elementRemoved',
        'messageSent',
        'messageFailed',
        'responseReceived',
        'beforeDomUpdate',
        'beforeElementUpdate',
        'afterElementUpdate',
        'afterDomUpdate',
        'interceptWireModelSetValue',
        'interceptWireModelAttachListener',
    ],

    bus: new MessageBus,

    register(name, callback) {
        if (! this.availableHooks.includes(name)) {
            throw `Livewire: Referencing unknown hook: [${name}]`
        }

        this.bus.register(name, callback)
    },

    call(name, ...params) {
        this.bus.call(name, ...params)
    }
}
