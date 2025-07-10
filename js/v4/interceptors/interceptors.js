import MessageBroker from '@/v4/requests/messageBroker.js'
import Interceptor from './interceptor.js'

class Interceptors {
    interceptors = new Map()

    constructor() {
        this.globalInterceptors = new Set()
        this.componentInterceptors = new Map()
    }

    add(callback, component = null, method = null) {
        let interceptorData = {callback, method}

        if (component === null) {
            this.globalInterceptors.add(interceptorData)

            return
        }

        let interceptors = this.componentInterceptors.get(component)

        if (!interceptors) {
            interceptors = new Set()

            this.componentInterceptors.set(component, interceptors)
        }

        interceptors.add(interceptorData)
    }

    fire(el, directive, component) {
        let method = directive.method

        for (let interceptorData of this.globalInterceptors) {
            let interceptor = new Interceptor(interceptorData.callback, interceptorData.method)

            console.log('firing', interceptor)

            interceptor.fire(el, directive, component)

            MessageBroker.addInterceptor(interceptor, component)
        }

        let componentInterceptors = this.componentInterceptors.get(component)

        if (!componentInterceptors) return

        for (let interceptorData of componentInterceptors) {
            if (interceptorData.method === method || interceptorData.method === null) {
                let interceptor = new Interceptor(interceptorData.callback, interceptorData.method)

                interceptor.fire(el, directive, component)

                MessageBroker.addInterceptor(interceptor, component)
            }
        }
    }
}

let instance = new Interceptors()

export default instance
