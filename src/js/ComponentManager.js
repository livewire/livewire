import Component from "./Component";
import store from './Store'
import LivewireElement from "./LivewireElement";

export default class ComponentManager {
    constructor(connection) {
        // I really need some kind of dependancy container so I don't have
        // to pass dependancies through objects like this.
        this.connection = connection
    }

    mount() {
        LivewireElement.rootComponentElementsWithNoParents().forEach(el => {
            const component = new Component(el, this.connection)

            store.componentsById[component.id] = component

            component.attachListenersAndAddChildComponents()
        })
    }

    destroy() {
        store.componentsById = {}
    }
}
