import store from './store'
import Component from "./component";
import LivewireElement from "./dom/element";
import NodeInitializer from "./node_initializer";
import EventAction from "./action/event";

export default class ComponentManager {
    constructor(connection) {
        this.connection = connection
        this.nodeInitializer = new NodeInitializer
        this.components = store
    }

    mount() {
        LivewireElement.rootComponentElements().forEach(el => {
            store.addComponent(
                new Component(el, this.nodeInitializer, this.connection)
            )
        })
    }

    destroy() {
        store.wipeComponents()
    }

    emitEvent(event, params) {
        store.componentsListeningForEvent(event).forEach(
            component => component.addAction(new EventAction(
                event, params
            ))
        )
    }
}
