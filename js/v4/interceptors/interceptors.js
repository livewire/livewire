import MessageBroker from '@/v4/requests/messageBroker.js'
import Interceptor from './interceptor.js'

class Interceptors {
    interceptors = new Map()

    constructor() {
        this.globalInterceptors = new Set()
        this.componentInterceptors = new Map()
    }

    add(callback, component = null, method = null) {
        let interceptor = new Interceptor(callback, method)

        if (component === null) {
            this.globalInterceptors.add(interceptor)

            return
        }

        let interceptors = this.componentInterceptors.get(component)

        if (!interceptors) {
            interceptors = new Set()
        }

        interceptors.add(interceptor)
    }

    fire(el, directive, component) {
        let method = directive.method

        for (let interceptor of this.globalInterceptors) {
            interceptor.fire(el, directive, component)

            MessageBroker.addInterceptor(interceptor, component)
        }

        let componentInterceptors = this.componentInterceptors.get(component)

        if (!componentInterceptors) return

        for (let interceptor of componentInterceptors) {
            if (interceptor.method === method || interceptor.method === null) {
                interceptor.fire(el, directive, component)

                MessageBroker.addInterceptor(interceptor, component)
            }
        }
    }
}

let instance = new Interceptors()

export default instance
