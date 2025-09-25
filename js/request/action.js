
export default class Action {
    handleReturn = () => {}

    constructor(component, method, params = [], metadata = {}, origin = {}) {
        this.component = component
        this.method = method
        this.params = params
        this.metadata = metadata
        this.origin = origin
    }
}
