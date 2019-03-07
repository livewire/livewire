import ElementDirectives from "./ElementDirectives";
const prefix = require('./Prefix.js')()

export default class LivewireElement {
    constructor(el) {
        this.el = el
        this.directives = new ElementDirectives(el)
    }

    static rootComponentElementsWithNoParents() {
        // In CSS, it's simple to select all elements that DO have a certain ancestor.
        // However, it's not simple (kinda impossible) to select elements that DONT have
        // a certain ancestor. Therefore, we will flip the logic (select all roots that have
        // have a root ancestor), then select all roots, then diff the two.

        // Convert NodeLists to Arrays so we can use ".includes()". Ew.
        const allEls = Array.prototype.slice.call(
            document.querySelectorAll(`[${prefix}\\:id]`)
        )
        const onlyChildEls = Array.prototype.slice.call(
            document.querySelectorAll(`[${prefix}\\:id] [${prefix}\\:id]`)
        )

        return allEls.filter(el => {
            return ! onlyChildEls.includes(el)
        }).map(el => {
            return new LivewireElement(el)
        })
    }

    transitionElementIn() {
        if (this.directives.has('transition')) {
            const transitionName = this.directives.get('transition').value

            this.el.classList.add(`${transitionName}-enter`)
            this.el.classList.add(`${transitionName}-enter-active`)

            setTimeout(() => {
                this.el.classList.remove(`${transitionName}-enter`)
                setTimeout(() => {
                    this.el.classList.remove(`${transitionName}-enter-active`)
                }, 500)
            }, 65)
        }
    }

    transitionElementOut() {
        if (this.directives.get('transition')) {
            const transitionName = this.directives.get('transition').value

            this.el.classList.add(`${transitionName}-leave-active`)

            setTimeout(() => {
                this.el.classList.add(`${transitionName}-leave-to`)
                    setTimeout(() => {
                        this.el.classList.remove(`${transitionName}-leave-active`)
                        this.el.classList.remove(`${transitionName}-leave-to`)
                        this.el.remove()
                    }, 500)
            }, 65)

            return false
        }
        return true
    }

    closestByAttribute(attribute) {
        return new LivewireElement(this.el.closest(`[${prefix}\\:${attribute}]`))
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

    shouldUpdateInputElementGivenItHasBeenUpdatedViaSync(dirtyInputs) {
        // This will need work. But is essentially "input persistance"
        const isInput = (this.el.tagName === 'INPUT' || this.el.tagName === 'TEXTAREA')

        if (isInput) {
            if (this.el.type === 'submit') {
                return true
            }

            if (this.directives.has('model')) {
                return Array.from(dirtyInputs).includes(this.directives.get('model').value)
            }

            return false
        }
    }

    valueFromInputOrCheckbox() {
        return this.el.type === 'checkbox'
            ? this.el.checked
            : this.el.value
    }

    // Forward isSameNode.
    isSameNode() {
        return this.el.isSameNode(...arguments)
    }

    // Forward addEventListener.
    addEventListener() {
        return this.el.addEventListener(...arguments)
    }
}
