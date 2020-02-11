import DOMElement from './dom_element'

/**
 * This is intended to isolate all native DOM operations. The operations that happen
 * one specific element will be instance methods, the operations you would normally
 * perform on the "document" (like "document.querySelector") will be static methods.
 */
export default class DOM {
    static rootComponentElements() {
        return Array.from(document.querySelectorAll(`[wire\\:id]`))
            .map(el => new DOMElement(el))
    }

    static rootComponentElementsWithNoParents() {
        // In CSS, it's simple to select all elements that DO have a certain ancestor.
        // However, it's not simple (kinda impossible) to select elements that DONT have
        // a certain ancestor. Therefore, we will flip the logic: select all roots that DO have
        // have a root ancestor, then select all roots that DONT, then diff the two.

        // Convert NodeLists to Arrays so we can use ".includes()". Ew.
        const allEls = Array.from(document.querySelectorAll(`[wire\\:id]`))
        const onlyChildEls = Array.from(document.querySelectorAll(`[wire\\:id] [wire\\:id]`))

        return allEls
            .filter(el => ! onlyChildEls.includes(el))
            .map(el => new DOMElement(el))
    }

    static allModelElementsInside(root) {
        return Array.from(
            root.querySelectorAll(`[wire\\:model]`)
        ).map(el => new DOMElement(el))
    }

    static getByAttributeAndValue(attribute, value) {
        return new DOMElement(document.querySelector(`[wire\\:${attribute}="${value}"]`))
    }
}
