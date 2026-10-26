import { arrayPull } from "@/utils"

let callbacks = []

export function interceptNavigate(callback) {
    callbacks.push(callback)

    return () => {
        arrayPull(callbacks, callback)
    }
}

// Each callback runs once per navigation, as it starts, so it can register
// handlers for the response that navigation receives...
export function getNavigateInterceptors(url) {
    let interceptors = callbacks.map(callback => new NavigateInterceptor(url, callback))

    return {
        // Run the onError handlers for a response that isn't a 2xx, and report
        // whether any of them asked to keep the current page instead...
        errorWasPrevented({ response, body }) {
            if (response.ok) return false

            let prevented = false

            interceptors.forEach(interceptor => {
                interceptor.onError({ response, body, preventDefault: () => prevented = true })
            })

            return prevented
        },
    }
}

class NavigateInterceptor {
    onError = () => {}

    constructor(url, callback) {
        callback({
            url,
            onError: (callback) => this.onError = callback,
        })
    }
}
