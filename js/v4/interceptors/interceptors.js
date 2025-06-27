class Interceptors {
    interceptors = new Map()

    constructor() {
        console.log('interceptors')
    }

    add(component, method, callback) {
        let interceptors = this.interceptors.get(component.id)

        if (!interceptors) {
            interceptors = new Map()
            this.interceptors.set(component.id, interceptors)
        }

        let methodInterceptors = interceptors.get(method)

        if (!methodInterceptors) {
            methodInterceptors = []
            interceptors.set(method, methodInterceptors)
        }

        methodInterceptors.push(callback)
    }

    getInterceptors(component, method) {
        let interceptors = this.interceptors.get(component.id)

        if (!interceptors) return []

        return interceptors.get(method) || []
    }

    fire(component, method, params) {
        let interceptors = this.getInterceptors(component, method)

        interceptors.forEach(interceptor => {
            interceptor(params)
        })
    }
}

let instance = new Interceptors()

export default instance
