import store from './store'
import Component from "./component";
import LivewireElement from "./dom/element";
import NodeInitializer from "./node_initializer";

export default class ComponentManager {
    constructor(connection) {
        this.connection = connection
        this.nodeInitializer = new NodeInitializer
    }

    mount() {
        LivewireElement.rootComponentElementsWithNoParents().forEach(el => {
            const component = store.addComponent(
                new Component(el, this.nodeInitializer, this.connection)
            )

            component.attachListenersAndProcessChildComponents(function(el) {
                return store.addComponent(
                    new Component(el, this.nodeInitializer, this.connection, this)
                )
            })
        })
    }

    destroy() {
        store.wipeComponents()
    }
}
