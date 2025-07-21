class Interceptor {
    beforeSend = () => {}
    afterSend = () => {}
    beforeResponse = () => {}
    afterResponse = () => {}
    beforeRender = () => {}
    afterRender = () => {}
    beforeMorph = () => {}
    afterMorph = () => {}
    onError = () => {}
    onFailure = () => {}
    onSuccess = () => {}
    onCancel = () => {}
    cancel = () => {}

    constructor(callback, action) {
        this.callback = callback
        this.action = action
        this.returned = () => {}
    }

    init(el, directive, component) {
        let request = {
            beforeSend: (callback) => this.beforeSend = callback,
            afterSend: (callback) => this.afterSend = callback,
            beforeResponse: (callback) => this.beforeResponse = callback,
            afterResponse: (callback) => this.afterResponse = callback,
            beforeRender: (callback) => this.beforeRender = callback,
            afterRender: (callback) => this.afterRender = callback,
            beforeMorph: (callback) => this.beforeMorph = callback,
            afterMorph: (callback) => this.afterMorph = callback,

            onError: (callback) => this.onError = callback,
            onFailure: (callback) => this.onFailure = callback,
            onSuccess: (callback) => this.onSuccess = callback,
            onCancel: (callback) => this.onCancel = callback,
        }

        let returned = this.callback({el, directive, component, request})

        if (returned && typeof returned === 'function') {
            this.returned = returned
        }
    }
}

export default Interceptor