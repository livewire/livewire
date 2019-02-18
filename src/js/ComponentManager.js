import Component from "./Component";
import rootsStore from './rootsStore'
const prefix = require('./prefix.js')()

export default class ComponentManager {
    constructor(nodeInitializer) {
        this.nodeInitializer = nodeInitializer
        this.components = {}
    }

    init() {
        this.rootComponentElements.forEach(el => {
            const component = new Component(el, this.nodeInitializer)
            rootsStore[component.id] = component
        })
    }

    get rootComponentElements() {
        // In CSS, it's simple to select all elements that DO have a certain ancestor.
        // However, it's not simple (kinda impossible) to select elements that DONT have
        // a certain ancestor. Therefore, we will flip the logic (select all roots that have
        // have a root ancestor), then select all roots, then take a diff of the two.

        // Convert NodeLists to Arrays. Ew.
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
