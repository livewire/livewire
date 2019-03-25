
const store = {
    componentsById: {},

    addComponent(component) {
        return this.componentsById[component.id] = component
    },

    findComponent(id) {
        return this.componentsById[id]
    },

    wipeComponents() {
        this.componentsById = {}
    }
}

export default store
