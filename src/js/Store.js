
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
    },

    componentsListeningForEvent(event) {
        return Object.keys(this.componentsById).map(key => {
            return this.componentsById[key]
        }).filter(component => {
            return component.events.includes(event)
        })
    },
}

export default store
