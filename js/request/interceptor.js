
export class Interceptor {
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

export class InterceptorRegistry {
    constructor() {
        this.globalInterceptors = new Set()
        this.componentInterceptors = new Map()
    }

    add(callback, component = null, method = null) {
        let interceptorData = {callback, method}

        if (component === null) {
            this.globalInterceptors.add(interceptorData)

            return () => {
                this.globalInterceptors.delete(interceptorData)
            }
        }

        let interceptors = this.componentInterceptors.get(component)

        if (! interceptors) {
            interceptors = new Set()

            this.componentInterceptors.set(component, interceptors)
        }

        interceptors.add(interceptorData)

        return () => {
            interceptors.delete(interceptorData)
        }
    }

    eachRelevantInterceptor(action, callback) {
        let interceptors = []

        // Collect all global interceptors
        for (let interceptorData of this.globalInterceptors) {
            interceptors.push(interceptorData)
        }

        // Collect matching component interceptors
        let componentInterceptors = this.componentInterceptors.get(action.component)
        if (componentInterceptors) {
            for (let interceptorData of componentInterceptors) {
                if (interceptorData.method === action.method || interceptorData.method === null) {
                    interceptors.push(interceptorData)
                }
            }
        }

        // Loop through and call the callback
        interceptors.forEach(callback)
    }
}
