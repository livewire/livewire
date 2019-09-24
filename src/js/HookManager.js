
export default {
    availableHooks: [
        'componentInitialized',
        'elementInitialized',
        'elementRemoved',
        'messageSent',
        'messageFailed',
        'responseReceived',
    ],

    hooks: {},

    register(name, callback) {
        if (! this.availableHooks.includes(name)) {
            throw `Livewire: Referencing unknown hook: [${name}]`
        }

        if (! this.hooks[name]) {
            this.hooks[name] = []
        }

        this.hooks[name].push(callback)
    },

    call(name, ...params) {
        (this.hooks[name] || []).forEach(callback => {
            callback(...params)
        })
    }
}
