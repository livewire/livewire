import { kebabCase } from '@/util'
import ModelAction from '@/action/model'
import MethodAction from '@/action/method'
import DOMElement from '@/dom/dom_element'
import store from '@/Store'

export default {
    initialize(el, component) {
        el.directives.all().forEach(directive => {
            switch (directive.type) {
                case 'init':
                    this.fireActionRightAway(el, directive, component)
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

        store.callHook('elementInitialized', el, component)
    },

    fireActionRightAway(el, directive, component) {
        const method = directive.value ? directive.method : '$refresh'

        component.addAction(new MethodAction(method, directive.params, el))
    },

    attachModelListener(el, directive, component) {
        // This is used by morphdom: morphdom.js:391
        el.el.isLivewireModel = true

        const isLazy = directive.modifiers.includes('lazy')
        const debounceIf = (condition, callback, time) => {
            return condition
                ? component.modelSyncDebounce(callback, time)
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
            const defaultEventType = el.isTextInput() ? 'input' : 'change'

            // If it's a text input and not .lazy, debounce, otherwise fire immediately.
            const event = isLazy ? 'change' : defaultEventType
            const handler = debounceIf(hasDebounceModifier || (el.isTextInput() && ! isLazy), e => {
                const model = directive.value
                const el = new DOMElement(e.target)
                const value = el.valueFromInput(component)

                component.addAction(new ModelAction(model, value, el))
            }, directive.durationOr(150))

            el.addEventListener(event, handler)

            component.addListenerForTeardown(() => {
                el.removeEventListener(event, handler)
            })
        }
    },

    attachDomListener(el, directive, component) {
        switch (directive.type) {
            case 'keydown':
            case 'keyup':
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
        if (directive.modifiers.includes('prefetch')) {
            el.addEventListener('mouseenter', () => {
                component.addPrefetchAction(new MethodAction(directive.method, directive.params, el))
            })
        }

        const event = directive.type
        const handler = e => {
            if (callback && callback(e) !== false) {
                return
            }

            component.callAfterModelDebounce(() => {
                const el = new DOMElement(e.target)

                directive.setEventContext(e)

                // This is outside the conditional below so "wire:click.prevent" without
                // a value still prevents default.
                this.preventAndStop(e, directive.modifiers)
                const method = directive.method
                const params = directive.params

                // Check for global event emission.
                if (method === '$emit') {
                    store.emit(...params)
                    return
                }

                if (directive.value) {
                    component.addAction(new MethodAction(method, params, el))
                }
            })
        }

        el.addEventListener(event, handler)

        component.addListenerForTeardown(() => {
            el.removeEventListener(event, handler)
        })
    },

    preventAndStop(event, modifiers) {
        modifiers.includes('prevent') && event.preventDefault()

        modifiers.includes('stop') && event.stopPropagation()
    },
}
