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
    sessionHasExpiredCallback: undefined,
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

    changeComponentId(component, newId) {
        let oldId = component.id

        component.id = newId
        component.fingerprint.id = newId

        this.componentsById[newId] = component

        delete this.componentsById[oldId]

        // Now go through any parents of this component and change
        // the component's child id references.
        this.components().forEach(component => {
            let children = component.serverMemo.children || {}

            Object.entries(children).forEach(([key, { id, tagName }]) => {
                if (id === oldId) {
                    children[key].id = newId
                }
            })
        })
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

    getClosestParentId(childId, subsetOfParentIds) {
        let distancesByParentId = {}

        subsetOfParentIds.forEach(parentId => {
            let distance = this.getDistanceToChild(parentId, childId)

            if (distance) distancesByParentId[parentId] = distance
        })

        let smallestDistance =  Math.min(...Object.values(distancesByParentId))

        let closestParentId

        Object.entries(distancesByParentId).forEach(([parentId, distance]) => {
            if (distance === smallestDistance) closestParentId = parentId
        })

        return closestParentId
    },

    getDistanceToChild(parentId, childId, distanceMemo = 1) {
        let parentComponent = this.findComponent(parentId)

        if (! parentComponent) return

        let childIds = parentComponent.childIds

        if (childIds.includes(childId)) return distanceMemo

        for (let i = 0; i < childIds.length; i++) {
            let distance = this.getDistanceToChild(childIds[i], childId, distanceMemo + 1)

            if (distance) return distance
        }
    }
}

export default store
