import ElementDirectives from "./directive_manager";
const prefix = require('./prefix.js')()

/**
 * This is intended to isolate all native DOM operations. The operations that happen
 * one specific element will be instance methods, the operations you would normally
 * perform on the "document" (like "document.querySelector") will be static methods.
 * Consider this a decorator for the ElementNode JavaScript object. (Hence the
 * method forwarding I have to do at the bottom)
 */
export default class DomElement {
    constructor(el) {
        this.el = el
        this.directives = new ElementDirectives(el)
    }

    nextFrame(fn) {
        requestAnimationFrame(() => {
            requestAnimationFrame(fn.bind(this));
        });
    }

    static get prefix() {
        return prefix
    }

    static rootComponentElements() {
        return Array.from(document.querySelectorAll(`[${prefix}\\:id]`))
            .map(el => new DomElement(el))
    }

    static rootComponentElementsWithNoParents() {
        // In CSS, it's simple to select all elements that DO have a certain ancestor.
        // However, it's not simple (kinda impossible) to select elements that DONT have
        // a certain ancestor. Therefore, we will flip the logic: select all roots that DO have
        // have a root ancestor, then select all roots that DONT, then diff the two.

        // Convert NodeLists to Arrays so we can use ".includes()". Ew.
        const allEls = Array.prototype.slice.call(
            document.querySelectorAll(`[${prefix}\\:id]`)
        )
        const onlyChildEls = Array.prototype.slice.call(
            document.querySelectorAll(`[${prefix}\\:id] [${prefix}\\:id]`)
        )

        return allEls
            .filter(el => ! onlyChildEls.includes(el))
            .map(el => new DomElement(el))
    }

    static allModelElementsInside(root) {
        return Array.from(
            root.querySelectorAll(`[${prefix}\\:model]`)
        ).map(el => new DomElement(el))
    }

    static getByAttributeAndValue(attribute, value) {
        return new DomElement(document.querySelector(`[${prefix}\\:${attribute}="${value}"]`))
    }

    static preserveActiveElement(callback) {
        const cached = document.activeElement

        callback()

        cached.focus()
    }

    rawNode() {
        return this.el
    }

    transitionElementIn() {
        if (! this.directives.has('transition')) return
        const directive = this.directives.get('transition')

        // If ".out" modifier is passed, don't fade in.
        if (directive.modifiers.includes('out') && ! directive.modifiers.includes('in')) {
            return true
        }

        if (directive.modifiers.includes('fade')) {
            this.el.style.opacity = 0
            this.el.style.transition = `opacity ${directive.durationOr(300) / 1000}s ease`

            this.nextFrame(() => {
                this.el.style.opacity = 1
            })

            return
        }

        const transitionName = directive.value

        this.el.classList.add(`${transitionName}-enter`)
        this.el.classList.add(`${transitionName}-enter-active`)

        this.nextFrame(() => {
            this.el.classList.remove(`${transitionName}-enter`)

            const duration = Number(getComputedStyle(this.el).transitionDuration.replace('s', '')) * 1000

            setTimeout(() => {
                this.el.classList.remove(`${transitionName}-enter-active`)
            }, duration)
        })
    }

    transitionElementOut(onDiscarded) {
        if (!this.directives.has('transition')) return true
        const directive = this.directives.get('transition')

        // If ".in" modifier is passed, don't fade out.
        if (directive.modifiers.includes('in') && ! directive.modifiers.includes('out')) {
            return true
        }

        if (directive.modifiers.includes('fade')) {
            this.nextFrame(() => {
                this.el.style.opacity = 0

                setTimeout(() => {
                    onDiscarded(this.el)

                    this.el.remove()
                }, directive.durationOr(300));
            })

            return false
        }

        const transitionName = directive.value

        this.el.classList.add(`${transitionName}-leave-active`)

        this.nextFrame(() => {
            this.el.classList.add(`${transitionName}-leave`)

            const duration = Number(getComputedStyle(this.el).transitionDuration.replace('s', '')) * 1000

            setTimeout(() => {
                onDiscarded(this.el)

                this.el.remove()
            }, duration)
        })

        return false
    }

    closestRoot() {
        return this.closestByAttribute('id')
    }

    closestByAttribute(attribute) {
        return new DomElement(this.el.closest(`[${prefix}\\:${attribute}]`))
    }

    isComponentRootEl() {
        return this.hasAttribute('id')
    }

    hasAttribute(attribute) {
        return this.el.hasAttribute(`${prefix}:${attribute}`)
    }

    getAttribute(attribute) {
        return this.el.getAttribute(`${prefix}:${attribute}`)
    }

    setAttribute(attribute, value) {
        return this.el.setAttribute(`${prefix}:${attribute}`, value)
    }

    isFocused() {
        return this.el === document.activeElement
    }

    preserveValueAttributeIfNotDirty(fromEl, dirtyInputs) {
        if (this.directives.missing('model')) return

        // If the input is not dirty && the input element is focused, keep the
        // value the same, but change other attributes.
        if (
            ! Array.from(dirtyInputs).includes(this.directives.get('model').value)
            && fromEl.isFocused()
        ) {
            // Transfer the current "fromEl" value (preserving / overriding it).
            this.el.value = fromEl.el.value
        }
    }

    isInput() {
        return this.el.tagName === 'INPUT'
            || this.el.tagName === 'TEXTAREA'
            || this.el.tagName === 'SELECT'
    }

    valueFromInput() {
        if (this.el.type === 'checkbox') {
            return this.el.checked
        } else if (this.el.tagName === 'SELECT' && this.el.multiple) {
            return this.getSelectValues()
        }

        return this.el.value
    }

    get ref() {
        return this.directives.get('ref')
            ? this.directives.get('ref').value
            : null
    }

    isSameNode(el) {
        // We need to drop down to the raw node if we are comparing
        // to another "DomElement" Instance.
        if (typeof el.rawNode === 'function') {
            return this.el.isSameNode(el.rawNode())
        }

        return this.el.isSameNode(el)
    }

    getAttributeNames() {
        return this.el.getAttributeNames(...arguments)
    }

    addEventListener() {
        return this.el.addEventListener(...arguments)
    }

    get classList() {
        return this.el.classList
    }

    querySelector() {
        return this.el.querySelector(...arguments)
    }

    querySelectorAll() {
        return this.el.querySelectorAll(...arguments)
    }

    setInputValueFromModel(component) {
        const modelString = this.directives.get('model').value
        const modelStringWithArraySyntaxForNumericKeys = modelString.replace(/\.([0-9]+)/, (match, num) => { return `[${num}]` })
        const modelValue = eval('component.data.'+modelStringWithArraySyntaxForNumericKeys)

        if (! modelValue) return

        this.setInputValue(modelValue)
    }

    setInputValue(value) {
        if (this.el.tagName === 'TEXTAREA') {
            // <textarea>'s don't use value properties, so we have to treat them differently.
            this.el.innerHTML = value
        } else if (this.el.type === 'checkbox') {
            this.el.checked = value
        } else if (this.el.tagName === 'SELECT') {
            this.updateSelect(value)
        } else if (! this.el.hasAttribute('value')) {
            this.el.setAttribute('value', value)
        }
    }

    getSelectValues() {
        return Array.from(this.el.options)
            .filter(option => option.selected)
            .map(option => { return option.value || option.text})
    }

    updateSelect(value) {
        const arrayWrappedValue = [].concat(value)
        Array.from(this.el.options).forEach(option => {
            option.selected = arrayWrappedValue.includes(option.value)
        })
    }
}
