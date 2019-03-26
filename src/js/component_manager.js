import store from './store'
import Component from "./component";
import TreeWalker from './dom/tree_walker'
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

            this.attachListenersAndProcessChildComponents((el, component) => {
                return store.addComponent(
                    new Component(el, this.nodeInitializer, this.connection, component)
                )
            }, component)
        })
    }

    attachListenersAndProcessChildComponents(callback, component) {
        const walker = new TreeWalker;

        walker.walk(component.el.rawNode(), (node) => {
            if (typeof node.hasAttribute !== 'function') return
            if (node.isSameNode(component.el.rawNode())) return

            const el = new LivewireElement(node)

            if (el.isComponentRootEl()) {
                const childComponent = callback(el, component)
                debugger
                this.attachListenersAndProcessChildComponents(callback, childComponent)
                return false;
            } else {
                this.nodeInitializer.initialize(el, component);
            }
        })
    }

    destroy() {
        store.wipeComponents()
    }
}
