
export default class Action {
    handleReturn = () => {}

    squashedActions = new Set()

    constructor(component, method, params = [], metadata = {}, origin = null) {
        this.component = component
        this.method = method
        this.params = params
        this.metadata = metadata
        this.origin = origin

        this.promise = new Promise((resolve, reject) => {
            this.promiseResolution = { resolve, reject }
        })
    }

    get fingerprint() {
        let componentId = this.component.id
        let method = this.method
        let params = JSON.stringify(this.params)
        let metadata = JSON.stringify(this.metadata)

        // btoa only supports Latin-1 characters, not UTF-8, so we need to encode the string first...
        return window.btoa(String.fromCharCode(...new TextEncoder().encode(componentId + method + params + metadata)))
    }

    isAsync() {
        let asyncMethods = this.component.snapshot.memo?.async || []

        let methodIsMarkedAsync = asyncMethods.includes(this.method)

        let actionIsAsync = this.origin?.directive?.modifiers.includes('async')

        return methodIsMarkedAsync || actionIsAsync
    }

    mergeMetadata(metadata) {
        this.metadata = { ...this.metadata, ...metadata }
    }

    rejectPromise(error) {
        // Resolving instead of rejecting to avoid unhandled promise rejection errors...
        // Should think about how we can handle this better...
        this.squashedActions.forEach(action => action.rejectPromise(error))
        this.promiseResolution.resolve()
    }

    addSquashedAction(action) {
        this.squashedActions.add(action)
    }

    resolvePromise(value) {
        this.squashedActions.forEach(action => action.resolvePromise(value))
        this.promiseResolution.resolve(value)
    }
}
