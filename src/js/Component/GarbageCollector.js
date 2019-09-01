
export default class GarbageCollector {
    constructor(component) {
        this.component = component
        this.destroyedComponentIds = []
    }

    add(id) {
        // If for some reason this component has already
        // been garbage collected, leave it be.
        if (this.destroyedComponentIds.includes(id)) return

        this.destroyedComponentIds.push(id)
    }
}
