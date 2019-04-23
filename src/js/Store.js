import EventAction from "./action/event";

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

    emit(event, ...params) {
        this.componentsListeningForEvent(event).forEach(
            component => component.addAction(new EventAction(
                event, params
            ))
        )
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
