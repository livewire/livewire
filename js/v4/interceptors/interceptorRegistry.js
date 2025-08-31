import MessageBroker from '@/v4/requests/messageBroker.js'
import Interceptor from './interceptor.js'

class InterceptorRegistry {
    interceptors = new Map()

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

        if (!interceptors) {
            interceptors = new Set()

            this.componentInterceptors.set(component, interceptors)
        }

        interceptors.add(interceptorData)

        return () => {
            interceptors.delete(interceptorData)
        }
    }

    fire(action) {
        for (let interceptorData of this.globalInterceptors) {
            let interceptor = new Interceptor(interceptorData.callback, action)

            MessageBroker.addInterceptor(interceptor, action.component)
        }

        let componentInterceptors = this.componentInterceptors.get(action.component)

        if (!componentInterceptors) return

        for (let interceptorData of componentInterceptors) {
            if (interceptorData.method === action.method || interceptorData.method === null) {
                let interceptor = new Interceptor(interceptorData.callback, action)

                MessageBroker.addInterceptor(interceptor, action.component)
            }
        }
    }
}

let instance = new InterceptorRegistry()

export default instance
