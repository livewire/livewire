import Component from "./Component";
import store from './Store'
const prefix = require('./Prefix')()

export default class ComponentManager {
    constructor(nodeInitializer) {
        // I really need some kind of dependancy container so I don't have
        // to pass dependancies through objects like this.
        this.nodeInitializer = nodeInitializer
    }

    init() {
        this.rootComponentElements.forEach(el => {
            const component = new Component(el, this.nodeInitializer)
            store.componentsById[component.id] = component
            component.attachListenersAndAddChildComponents()
        })
    }

    destroy() {
        store.componentsById = {}
    }

    get rootComponentElements() {
        // In CSS, it's simple to select all elements that DO have a certain ancestor.
        // However, it's not simple (kinda impossible) to select elements that DONT have
        // a certain ancestor. Therefore, we will flip the logic (select all roots that have
        // have a root ancestor), then select all roots, then diff the two.

        // Convert NodeLists to Arrays so we can use ".includes()". Ew.
        const allEls = Array.prototype.slice.call(
            document.querySelectorAll(`[${prefix}\\:root-id]`)
        )
        const onlyChildEls = Array.prototype.slice.call(
            document.querySelectorAll(`[${prefix}\\:root-id] [${prefix}\\:root-id]`)
        )

        return allEls.filter(el => {
            return ! onlyChildEls.includes(el)
        })
    }
}
