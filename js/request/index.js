import { InterceptorRegistry, Interceptor } from './interceptor.js'
import messageBroker from './messageBroker.js'
import Action from './action.js'

// Export the new origin management function
export { setNextActionOrigin } from './actionOrigin.js'

let interceptors = new InterceptorRegistry

export function intercept(callback, component = null, method = null) {
    return interceptors.add(callback, component, method)
}

// Core action firing - single entry point
export function fireAction(component, method, params = [], metadata = {}) {
    let action = new Action(component, method, params, metadata)

    let message = messageBroker.getMessage(component)

    // Create and fire interceptors with full context
    interceptors.eachRelevantInterceptor(action, (interceptorData) => {
        let interceptor = new Interceptor(interceptorData.callback, action)

        message.addInterceptor(interceptor)
    })

    // Add to message broker and return promise
    return messageBroker.addAction(action)
}
