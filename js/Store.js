import EventAction from '@/action/event'
import HookManager from '@/HookManager'
import MessageBus from './MessageBus'
import DirectiveManager from './DirectiveManager'

const store = {
    componentsById: {},
    listeners: new MessageBus(),
    initialRenderIsFinished: false,
    livewireIsInBackground: false,
    livewireIsOffline: false,
    sessionHasExpired: false,
    directives: DirectiveManager,
    hooks: HookManager,
    onErrorCallback: () => { },

    components() {
        return Object.keys(this.componentsById).map(key => {
            return this.componentsById[key]
        })
    },

    addComponent(component) {
        return (this.componentsById[component.id] = component)
    },

    findComponent(id) {
        return this.componentsById[id]
    },

    getComponentsByName(name) {
        return this.components().filter(component => {
            return component.name === name
        })
    },

    hasComponent(id) {
        return !!this.componentsById[id]
    },

    tearDownComponents() {
        this.components().forEach(component => {
            this.removeComponent(component)
        })
    },

    on(event, callback) {
        this.listeners.register(event, callback)
    },

    emit(event, ...params) {
        this.listeners.call(event, ...params)

        this.componentsListeningForEvent(event).forEach(component =>
            component.addAction(new EventAction(event, params))
        )
    },

    emitUp(el, event, ...params) {
        this.componentsListeningForEventThatAreTreeAncestors(
            el,
            event
        ).forEach(component =>
            component.addAction(new EventAction(event, params))
        )
    },

    emitSelf(componentId, event, ...params) {
        let component = this.findComponent(componentId)

        if (component.listeners.includes(event)) {
            component.addAction(new EventAction(event, params))
        }
    },

    emitTo(componentName, event, ...params) {
        let components = this.getComponentsByName(componentName)

        components.forEach(component => {
            if (component.listeners.includes(event)) {
                component.addAction(new EventAction(event, params))
            }
        })
    },

    componentsListeningForEventThatAreTreeAncestors(el, event) {
        var parentIds = []

        var parent = el.parentElement.closest('[wire\\:id]')

        while (parent) {
            parentIds.push(parent.getAttribute('wire:id'))

            parent = parent.parentElement.closest('[wire\\:id]')
        }

        return this.components().filter(component => {
            return (
                component.listeners.includes(event) &&
                parentIds.includes(component.id)
            )
        })
    },

    componentsListeningForEvent(event) {
        return this.components().filter(component => {
            return component.listeners.includes(event)
        })
    },

    registerDirective(name, callback) {
        this.directives.register(name, callback)
    },

    registerHook(name, callback) {
        this.hooks.register(name, callback)
    },

    callHook(name, ...params) {
        this.hooks.call(name, ...params)
    },

    removeComponent(component) {
        // Remove event listeners attached to the DOM.
        component.tearDown()
        // Remove the component from the store.
        delete this.componentsById[component.id]
    },

    onError(callback) {
        this.onErrorCallback = callback
    },
}

export default store
