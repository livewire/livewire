import store from './Store'
import Component from "./Component";
import LivewireElement from "./LivewireElement";
import NodeInitializer from "./NodeInitializer";

export default class ComponentManager {
    constructor(connection) {
        window.store = store
        // I really need some kind of dependancy container so I don't have
        // to pass dependancies through objects like this.
        this.nodeInitializer = new NodeInitializer
        this.connection = connection
    }

    mount() {
        LivewireElement.rootComponentElementsWithNoParents().forEach(el => {
            const component = store.addComponent(new Component(el, this.nodeInitializer, this.connection))

            component.attachListenersAndProcessChildComponents(function(childEl) {
                return store.addComponent(new Component(childEl, this.nodeInitializer, this.connection, this))
            })
        })
    }

    destroy() {
        store.wipeComponents()
    }
}
