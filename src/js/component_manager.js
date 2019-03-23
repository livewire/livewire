import store from './store'
import Component from "./component";
import LivewireElement from "./dom/element";
import NodeInitializer from "./node_initializer";

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
