import ElementDirectives from './directive_manager'
import get from 'get-value'
import store from '@/Store'

/**
 * Consider this a decorator for the ElementNode JavaScript object. (Hence the
 * method forwarding I have to do at the bottom)
 */
export default class DOMElement {
    constructor(el) {
        this.el = el
        this.directives = new ElementDirectives(el)
    }

    nextFrame(fn) {
        requestAnimationFrame(() => {
            requestAnimationFrame(fn.bind(this))
        })
    }

    rawNode() {
        return this.el
    }

    closestRoot() {
        return this.closestByAttribute('id')
    }

    closestByAttribute(attribute) {
        const closestEl = this.el.closest(`[wire\\:${attribute}]`)

        if (!closestEl) {
            throw `
Livewire Error:\n
Cannot find parent element in DOM tree containing attribute: [wire:${attribute}].\n
Usually this is caused by Livewire's DOM-differ not being able to properly track changes.\n
Reference the following guide for common causes: https://laravel-livewire.com/docs/troubleshooting \n
Referenced element:\n
${this.el.outerHTML}
`
        }

        return new DOMElement(closestEl)
    }

    isComponentRootEl() {
        return this.hasAttribute('id')
    }

    hasAttribute(attribute) {
        return this.el.hasAttribute(`wire:${attribute}`)
    }

    getAttribute(attribute) {
        return this.el.getAttribute(`wire:${attribute}`)
    }

    removeAttribute(attribute) {
        return this.el.removeAttribute(`wire:${attribute}`)
    }

    setAttribute(attribute, value) {
        return this.el.setAttribute(`wire:${attribute}`, value)
    }

    isFocused() {
        return this.el === document.activeElement
    }

    hasFocus() {
        return this.el === document.activeElement
    }

    isInput() {
        return ['INPUT', 'TEXTAREA', 'SELECT'].includes(
            this.el.tagName.toUpperCase()
        )
    }

    isTextInput() {
        return (
            ['INPUT', 'TEXTAREA'].includes(this.el.tagName.toUpperCase()) &&
            !['checkbox', 'radio'].includes(this.el.type)
        )
    }

    valueFromInput(component) {
        if (this.el.type === 'checkbox') {
            const modelName = this.directives.get('model').value
            var modelValue = get(component.data, modelName)

            if (Array.isArray(modelValue)) {
                if (this.el.checked) {
                    modelValue = modelValue.includes(this.el.value)
                        ? modelValue
                        : modelValue.concat(this.el.value)
                } else {
                    modelValue = modelValue.filter(
                        item => item !== this.el.value
                    )
                }

                return modelValue
            }

            if (this.el.checked) {
                return this.el.getAttribute('value') || true
            } else {
                return false
            }
        } else if (this.el.tagName === 'SELECT' && this.el.multiple) {
            return this.getSelectValues()
        }

        return this.el.value
    }

    setInputValueFromModel(component) {
        const modelString = this.directives.get('model').value
        const modelValue = get(component.data, modelString)
        if (modelValue === undefined) return
        // Don't manually set file input's values.
        if (
            this.el.tagName.toLowerCase() === 'input' &&
            this.el.type === 'file'
        )
            return

        this.setInputValue(modelValue)
    }

    setInputValue(value) {
        store.callHook('interceptWireModelSetValue', this, value)

        if (this.el.type === 'radio') {
            this.el.checked = this.el.value == value
        } else if (this.el.type === 'checkbox') {
            if (Array.isArray(value)) {
                // I'm purposely not using Array.includes here because it's
                // strict, and because of Numeric/String mis-casting, I
                // want the "includes" to be "fuzzy".
                let valueFound = false
                value.forEach(val => {
                    if (val == this.el.value) {
                        valueFound = true
                    }
                })

                this.el.checked = valueFound
            } else {
                this.el.checked = !!value
            }
        } else if (this.el.tagName === 'SELECT') {
            this.updateSelect(value)
        } else {
            this.el.value = value
        }
    }

    getSelectValues() {
        return Array.from(this.el.options)
            .filter(option => option.selected)
            .map(option => {
                return option.value || option.text
            })
    }

    updateSelect(value) {
        const arrayWrappedValue = [].concat(value).map(value => {
            return value + ''
        })

        Array.from(this.el.options).forEach(option => {
            option.selected = arrayWrappedValue.includes(option.value)
        })
    }

    get ref() {
        return this.directives.has('ref')
            ? this.directives.get('ref').value
            : null
    }

    isSameNode(el) {
        // We need to drop down to the raw node if we are comparing
        // to another "DOMElement" Instance.
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

    removeEventListener() {
        return this.el.removeEventListener(...arguments)
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
}
