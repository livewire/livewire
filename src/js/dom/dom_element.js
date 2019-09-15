import ElementDirectives from "./directive_manager"
import get from 'get-value'
const prefix = require('./prefix.js')()

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
            requestAnimationFrame(fn.bind(this));
        });
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
            this.fadeIn(directive)
            return
        }

        if (directive.modifiers.includes('slide')) {
            this.slideIn(directive)
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
            this.fadeOut(directive, onDiscarded)

            return false
        }

        if (directive.modifiers.includes('slide')) {
            this.slideOut(directive, onDiscarded)

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

    fadeIn(directive) {
        this.el.style.opacity = 0
        this.el.style.transition = `opacity ${directive.durationOr(300) / 1000}s ease`

        this.nextFrame(() => {
            this.el.style.opacity = 1
        })
    }

    slideIn(directive) {
        const directions = {
            up: 'translateY(10px)',
            down: 'translateY(-10px)',
            left: 'translateX(-10px)',
            right: 'translateX(10px)',
        }

        this.el.style.opacity = 0
        this.el.style.transform = directions[directive.cardinalDirectionOr('right')]
        this.el.style.transition = `opacity ${directive.durationOr(300) / 1000}s ease, transform ${directive.durationOr(300) / 1000}s ease`

        this.nextFrame(() => {
            this.el.style.opacity = 1
            this.el.style.transform = ``
        })
    }

    fadeOut(directive, onDiscarded) {
        this.nextFrame(() => {
            this.el.style.opacity = 0

            setTimeout(() => {
                onDiscarded(this.el)

                this.el.remove()
            }, directive.durationOr(300));
        })
    }

    slideOut(directive, onDiscarded) {
        const directions = {
            up: 'translateY(10px)',
            down: 'translateY(-10px)',
            left: 'translateX(-10px)',
            right: 'translateX(10px)',
        }

        this.nextFrame(() => {
            this.el.style.opacity = 0
            this.el.style.transform = directions[directive.cardinalDirectionOr('right')]

            setTimeout(() => {
                onDiscarded(this.el)

                this.el.remove()
            }, directive.durationOr(300));
        })
    }

    closestRoot() {
        return this.closestByAttribute('id')
    }

    closestByAttribute(attribute) {
        return new DOMElement(this.el.closest(`[${prefix}\\:${attribute}]`))
    }

    isComponentRootEl() {
        return this.hasAttribute('id')
    }

    isVueComponent() {
        return !! this.asVueComponent()
    }

    asVueComponent() {
        return this.rawNode().__vue__
    }

    hasAttribute(attribute) {
        return this.el.hasAttribute(`${prefix}:${attribute}`)
    }

    getAttribute(attribute) {
        return this.el.getAttribute(`${prefix}:${attribute}`)
    }

    removeAttribute(attribute) {
        return this.el.removeAttribute(`${prefix}:${attribute}`)
    }

    setAttribute(attribute, value) {
        return this.el.setAttribute(`${prefix}:${attribute}`, value)
    }

    isFocused() {
        return this.el === document.activeElement
    }

    hasFocus() {
        return this.el === document.activeElement
    }

    isInput() {
        return ['INPUT', 'TEXTAREA', 'SELECT'].includes(this.el.tagName.toUpperCase())
    }

    isTextInput() {
        return ['INPUT', 'TEXTAREA'].includes(this.el.tagName.toUpperCase())
            && ! ['checkbox', 'radio'].includes(this.el.type)
    }

    valueFromInput(component) {
        if (this.el.type === 'checkbox') {
            const modelName =  this.directives.get('model').value
            var modelValue = get(component.data, modelName)

            if (Array.isArray(modelValue)) {
                if (this.el.checked) {
                    modelValue = modelValue.includes(this.el.value) ? modelValue : modelValue.concat(this.el.value)
                } else {
                    modelValue = modelValue.filter(item => item !== this.el.value)
                }

                return modelValue
            }

            return this.el.checked
        } else if (this.el.tagName === 'SELECT' && this.el.multiple) {
            return this.getSelectValues()
        }

        return this.el.value
    }

    setInputValueFromModel(component) {
        const modelString = this.directives.get('model').value
        const modelValue = get(component.data, modelString)
        if (modelValue === undefined) return

        this.setInputValue(modelValue)
    }

    setInputValue(value) {
        if (this.rawNode().__vue__) {
            // If it's a vue component pass down the value prop.
            // Also, Vue will throw a warning because we are programmaticallly
            // setting a prop, we need to silence that.
            const originalSilent = window.Vue.config.silent
            window.Vue.config.silent = true
            this.rawNode().__vue__.$props.value = value
            window.Vue.config.silent = originalSilent
        } else if (this.el.type === 'radio') {
            this.el.checked = this.el.value == value
        } else if (this.el.type === 'checkbox') {
            if (Array.isArray(value)) {
                if (value.includes(this.el.value)) {
                    this.el.checked = true
                }
            } else {
                this.el.checked = !! value
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
            .map(option => { return option.value || option.text })
    }

    updateSelect(value) {
        const arrayWrappedValue = [].concat(value).map(value => { return value +'' })

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
