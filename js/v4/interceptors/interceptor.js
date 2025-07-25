class Interceptor {
    beforeSend = () => {}
    afterSend = () => {}
    beforeResponse = () => {}
    afterResponse = () => {}
    beforeRender = () => {}
    afterRender = () => {}
    beforeMorph = () => {}
    afterMorph = () => {}
    beforeMorphIsland = () => {}
    afterMorphIsland = () => {}
    onError = () => {}
    onFailure = () => {}
    onSuccess = () => {}
    onCancel = () => {}

    // If cancel is called before a message is prepared, then this flag
    // instructs the message to cancel itself when it is loaded...
    hasBeenCancelled = false

    cancel = () => {
        this.hasBeenCancelled = true
    }

    constructor(callback, action) {
        let request = this.requestObject()

        let returned = callback({ action, component: action.component, request, el: action.el, directive: action.directive })

        this.returned = (returned && typeof returned === 'function') ? returned : () => {}
    }

    requestObject() {
        return {
            beforeSend: (callback) => this.beforeSend = callback,
            afterSend: (callback) => this.afterSend = callback,
            beforeResponse: (callback) => this.beforeResponse = callback,
            afterResponse: (callback) => this.afterResponse = callback,
            beforeRender: (callback) => this.beforeRender = callback,
            afterRender: (callback) => this.afterRender = callback,
            beforeMorph: (callback) => this.beforeMorph = callback,
            afterMorph: (callback) => this.afterMorph = callback,
            beforeMorphIsland: (callback) => this.beforeMorphIsland = callback,
            afterMorphIsland: (callback) => this.afterMorphIsland = callback,

            onError: (callback) => this.onError = callback,
            onFailure: (callback) => this.onFailure = callback,
            onSuccess: (callback) => this.onSuccess = callback,
            onCancel: (callback) => this.onCancel = callback,

            cancel: () => this.cancel()
        }
    }
}

export default Interceptor