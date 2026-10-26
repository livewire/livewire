import { arrayPull } from "@/utils"

let callbacks = []

export function interceptNavigate(callback) {
    callbacks.push(callback)

    return () => {
        arrayPull(callbacks, callback)
    }
}

// Each callback runs once per navigation, as it starts, so it can register
// handlers for what happens to that navigation...
export function getNavigateInterceptors(url) {
    let interceptors = callbacks.map(callback => new NavigateInterceptor(url, callback))

    let finished = false

    // Run one kind of handler on every interceptor, and report whether any
    // of them asked to keep the current page instead...
    let invokeAndCheckIfPrevented = (handler, payload) => {
        // A navigation superseded by a newer one has already finished, but its
        // response can still arrive. Its interceptors have heard the end...
        if (finished) return false

        let prevented = false

        interceptors.forEach(interceptor => {
            interceptor[handler]({ ...payload, preventDefault: () => prevented = true })
        })

        return prevented
    }

    return {
        redirectWasPrevented({ url, response, body }) {
            if (! response.redirected) return false

            return invokeAndCheckIfPrevented('onRedirect', { url, response, body })
        },

        errorWasPrevented({ response, body }) {
            if (response.ok) return false

            return invokeAndCheckIfPrevented('onError', { response, body })
        },

        failureWasPrevented({ error }) {
            return invokeAndCheckIfPrevented('onFailure', { error })
        },

        // A navigation can end several ways at once (cancelled, then failed),
        // but its interceptors hear about the end only once...
        finish() {
            if (finished) return

            finished = true

            interceptors.forEach(interceptor => interceptor.onFinish())
        },
    }
}

class NavigateInterceptor {
    onRedirect = () => {}
    onError = () => {}
    onFailure = () => {}
    onFinish = () => {}

    constructor(url, callback) {
        callback({
            url,
            onRedirect: (callback) => this.onRedirect = callback,
            onError: (callback) => this.onError = callback,
            onFailure: (callback) => this.onFailure = callback,
            onFinish: (callback) => this.onFinish = callback,
        })
    }
}
