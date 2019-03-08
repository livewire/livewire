import store from './Store'
import Component from "./Component";
import LivewireElement from "./LivewireElement";
import NodeInitializer from "./NodeInitializer";

export default class ComponentManager {
    constructor(connection) {
        // I really need some kind of dependancy container so I don't have
        // to pass dependancies through objects like this.
        this.nodeInitializer = new NodeInitializer(connection)
    }

    mount() {
        LivewireElement.rootComponentElementsWithNoParents().forEach(el => {
            const component = store.addComponent(new Component(el, this.nodeInitializer))

            component.attachListenersAndProcessChildComponents(function(childEl) {
                store.addComponent(new Component(el, this.nodeInitializer, this))
            })
        })
    }

    destroy() {
        store.wipeComponents()
    }
}
