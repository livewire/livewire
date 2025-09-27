import { pullNextActionOrigin } from './actionOrigin.js'

export default class Action {
    handleReturn = () => {}

    constructor(component, method, params = [], metadata = {}) {
        this.component = component
        this.method = method
        this.params = params
        this.metadata = metadata

        // Automatically pull any pending origin
        this.origin = pullNextActionOrigin()

        // For backwards compatibility - keep context as alias to metadata
        this.context = this.metadata

        // For backwards compatibility - keep el and directive on action
        this.el = this.origin.el || null
        this.directive = this.origin.directive || null
    }

    // Backwards compatibility method
    addContext(context) {
        this.metadata = {...this.metadata, ...context}
        this.context = this.metadata
    }

    getContainer() {
        return 'island' in this.metadata ? 'island' : 'component'
    }
}
