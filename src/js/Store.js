import EventAction from "@/action/event";

const store = {
    componentsById: {},
    listeners: {},
    beforeDomUpdateCallback: () => {},
    afterDomUpdateCallback: () => {},

    components() {
        return Object.keys(this.componentsById).map(key => this.componentsById[key])
    },

    addComponent(component) {
        return this.componentsById[component.id] = component
    },

    findComponent(id) {
        return this.componentsById[id]
    },

    hasComponent(id) {
        return !! this.componentsById[id]
    },

    tearDownComponents() {
        this.components().forEach(component => {
            component.tearDown()
            delete this.componentsById[component.id]
        })
    },

    on(event, callback) {
        if (this.listeners[event] !== undefined) {
            this.listeners[event].push(callback)
        } else {
            this.listeners[event] = [callback]
        }
    },

    emit(event, ...params) {
        if (this.listeners[event] !== undefined) {
            this.listeners[event].forEach(callback => callback(...params))
        }

        this.componentsListeningForEvent(event).forEach(
            component => component.addAction(new EventAction(
                event, params
            ))
        )
    },

    componentsListeningForEvent(event) {
        return this.components().filter(({ events }) => events.includes(event))
    },

    beforeDomUpdate(callback) {
        this.beforeDomUpdateCallback = callback
    },

    afterDomUpdate(callback) {
        this.afterDomUpdateCallback = callback
    },
}

export default store
