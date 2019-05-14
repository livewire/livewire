import { debounce, kebabCase } from './util'
import ModelAction from './action/model'
import MethodAction from './action/method'
import DOMElement from './dom/dom_element'
import store from './store'

export default {
    initialize(el, component) {
        // Parse out "direcives", "modifiers", and "value" from livewire attributes.
        el.directives.all().forEach(directive => {
            switch (directive.type) {
                case 'loading':
                    this.registerElementForLoading(el, directive, component)
                    break;

                case 'poll':
                    this.fireActionOnInterval(el, directive, component)
                    break;

                case 'model':
                    el.setInputValueFromModel(component)
                    this.attachModelListener(el, directive, component)
                    break;

                default:
                    this.attachDomListener(el, directive, component)
                    break;
            }
        })
    },

    registerElementForLoading(el, directive, component) {
        const refName = el.directives.get('target')
            && el.directives.get('target').value

        component.addLoadingEl(
            el,
            directive.value,
            refName,
            directive.modifiers.includes('remove')
        )
    },

    fireActionOnInterval(el, directive, component) {
        const method = directive.method || '$refresh'

        setInterval(() => {
            component.addAction(new MethodAction(method, directive.params, el))
        }, directive.durationOr(500));
    },

    attachModelListener(el, directive, component) {
        const isLazy = directive.modifiers.includes('lazy')
        const debounceIf = (condition, callback, time) => {
            return condition
                ? debounce(callback, time)
                : callback
        }
        const hasDebounceModifier = directive.modifiers.includes('debounce')

        // If it's a Vue component, listen for Vue input event emission.
        if (el.isVueComponent()) {
            el.asVueComponent().$on('input', debounceIf(hasDebounceModifier, e => {
                const model = directive.value
                const value = e

                component.addAction(new ModelAction(model, value, el))
            }, directive.durationOr(150)))
        } else {
            // If it's a text input and not .lazy, debounce, otherwise fire immediately.
            el.addEventListener(isLazy ? 'change' : 'input', debounceIf(hasDebounceModifier || (el.isTextInput() && ! isLazy), e => {
                const model = directive.value
                const el = new DOMElement(e.target)
                const value = el.valueFromInput()

                component.addAction(new ModelAction(model, value, el))
            }, directive.durationOr(150)))
        }
    },

    attachDomListener(el, directive, component) {
        switch (directive.type) {
            case 'keydown':
                this.attachListener(el, directive, component, (e) => {
                    // Only handle listener if no, or matching key modifiers are passed.
                    return ! (directive.modifiers.length === 0
                        || directive.modifiers.includes(kebabCase(e.key)))
                })
                break;
            default:
                this.attachListener(el, directive, component)
                break;
        }
    },

    attachListener(el, directive, component, callback) {
        el.addEventListener(directive.type, (e => {
            if (callback && callback(e) !== false) {
                return
            }

            const el = new DOMElement(e.target)

            // This is outside the conditional below so "wire:click.prevent" without
            // a value still prevents default.
            this.preventAndStop(e, directive.modifiers)

            // Check for global event emission.
            if (directive.value.match(/\$emit\(.*\)/)) {
                const tempStoreForEval = store
                eval(directive.value.replace(/\$emit\((.*)\)/, (match, group1) => {
                    return 'tempStoreForEval.emit('+group1+')'
                }))
                return
            }

            if (directive.value) {
                directive.setEventContext(e)
                component.addAction(new MethodAction(directive.method, directive.params, el))
            }
        }))
    },

    preventAndStop(event, modifiers) {
        modifiers.includes('prevent') && event.preventDefault()

        modifiers.includes('stop') && event.stopPropagation()
    },
}
