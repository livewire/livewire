class Interceptors {
    interceptors = new Map()

    constructor() {
        console.log('interceptors')
        this.globalInterceptors = new Set()
        this.componentInterceptors = new Map()
    }

    add(callback, component = null, method = null) {
        if (component === null) {
            this.globalInterceptors.add(callback)

            return
        }

        let interceptors = this.componentInterceptors.get(component)

        if (!interceptors) {
            interceptors = new Set()
        }

        interceptors.add({ method, callback })
    }

    fire(el, directive, component) {
        let method = directive.method

        for (let interceptor of this.globalInterceptors) {
            interceptor({el, directive, component})
        }

        let componentInterceptors = this.componentInterceptors.get(component)

        if (!componentInterceptors) return

        for (let interceptor of componentInterceptors) {
            if (interceptor.method === method || interceptor.method === null) {
                interceptor.callback({el, directive, component})
            }
        }
    }
}

let instance = new Interceptors()

export default instance
