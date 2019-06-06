import EventAction from "./action/event";

const store = {
    componentsById: {},
    listeners: {},

    addComponent(component) {
        return this.componentsById[component.id] = component
    },

    findComponent(id) {
        return this.componentsById[id]
    },

    wipeComponents() {
        this.componentsById = {}
    },

    on(event, callback) {
        if (this.listeners[event] !== undefined) {
            this.listeners[event].push(callback)
        } else {
            this.listeners[event] = [callback]
        }
    },

    emit(event, ...params) {
        Object.keys(this.listeners).forEach(event => {
            this.listeners[event].forEach(callback => callback(...params))
        })

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
