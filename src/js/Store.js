import EventAction from "@/action/event";
import HookManager from "@/HookManager";

const store = {
    componentsById: {},
    listeners: {},
    beforeDomUpdateCallback: () => {},
    afterDomUpdateCallback: () => {},
    livewireIsInBackground: false,
    livewireIsOffline: false,
    hooks: HookManager,

    components() {
        return Object.keys(this.componentsById).map(key => {
            return this.componentsById[key]
        })
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
            this.removeComponent(component)
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
        return this.components().filter(component => {
            return component.events.includes(event)
        })
    },

    registerHook(name, callback) {
        this.hooks.register(name, callback)
    },

    callHook(name, ...params) {
        this.hooks.call(name, ...params)
    },

    beforeDomUpdate(callback) {
        this.beforeDomUpdateCallback = callback
    },

    afterDomUpdate(callback) {
        this.afterDomUpdateCallback = callback
    },

    removeComponent(component) {
        // Remove event listeners attached to the DOM.
        component.tearDown()
        // Remove the component from the store.
        delete this.componentsById[component.id]
        // Add the component the queue for backend cache garbage collection.
        this.addComponentForCollection(component.id)
    },

    initializeGarbageCollection()
    {
        if (! window.localStorage.hasOwnProperty(this.localStorageKey())) {
            window.localStorage.setItem(this.localStorageKey(), '')
        }
    },

    getComponentsForCollection() {
        const storedString = atob(window.localStorage.getItem(this.localStorageKey()))

        if (storedString === '') return []

        return storedString.split(',')
    },

    addComponentForCollection(componentId) {
        return window.localStorage.setItem(this.localStorageKey(),
            btoa(this.getComponentsForCollection().concat(componentId).join(','))
        )
    },

    setComponentsAsCollected(componentIds) {
        window.localStorage.setItem(this.localStorageKey(), btoa(this.getComponentsForCollection().filter(
            id => ! componentIds.includes(id)
        ).join(',')))
    },

    localStorageKey() {
        return 'livewire'
    }
}

export default store
