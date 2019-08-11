import { kebabCase } from '@/util'
import ModelAction from '@/action/model'
import MethodAction from '@/action/method'
import DOMElement from '@/dom/dom_element'
import store from '@/Store'

export default {
    initialize(el, component) {
        // Parse out "direcives", "modifiers", and "value" from livewire attributes.
        el.directives.all().forEach(directive => {
            switch (directive.type) {
                case 'loading':
                    this.registerElementForLoading(el, directive, component)
                    break;

                case 'dirty':
                    this.registerElementForDirty(el, directive, component)
                    break;

                case 'poll':
                    this.fireActionOnInterval(el, directive, component)
                    break;

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
    },

    registerElementForLoading(el, directive, component) {
        const refNames = el.directives.get('target')
            && el.directives.get('target').value.split(',').map(s => s.trim())

        component.addLoadingEl(
            el,
            directive.value,
            refNames,
            directive.modifiers.includes('remove')
        )
    },

    registerElementForDirty(el, directive, component) {
        const refNames = el.directives.get('target')
            && el.directives.get('target').value.split(',').map(s => s.trim())

        if (refNames) {
            component.addDirtyElsByRef(
                el,
                directive.value,
                refNames,
                directive.modifiers.includes('remove')
            )
        }

        const dirtyHander = (e) => {
            const el = new DOMElement(e.target)

            const refEls = component.dirtyElsByRef[el.ref] ? component.dirtyElsByRef[el.ref].map(el => el.el).flat() : []
            const allEls = [el].concat(refEls)

            const dirty = el.valueFromInput() !== component.data[el.directives.get('model').value].toString()

            allEls.forEach(el => {
                const directive = el.directives.get('dirty')

                if (directive.modifiers.includes('class')) {
                    const classes = directive.value.split(' ')
                    if (directive.modifiers.includes('remove') !== dirty) {
                        el.classList.add(...classes)
                    } else {
                        el.classList.remove(...classes)
                    }
                } else if (directive.modifiers.includes('attr')) {
                    if (directive.modifiers.includes('remove') !== dirty) {
                        el.setAttribute(directive.value, true)
                    } else {
                        el.removeAttrsibute(directive.value)
                    }
                } else if (!el.directives.get('model') && !el.directives.get('ref')) {
                    el.el.style.display = dirty ? 'inline-block' : 'none'
                }
            })
        }

        // An element can only be detected as dirty if it is wire:modelled.
        if (el.directives.get('model')) {
            el.addEventListener('input', dirtyHander)

            component.addListenerForTeardown(() => {
                el.removeEventListener('input', dirtyHandler)
            })
        }

    },

    fireActionOnInterval(el, directive, component) {
        const method = directive.method || '$refresh'

        setInterval(() => {
            component.addAction(new MethodAction(method, directive.params, el))
        }, directive.durationOr(500));
    },

    fireActionRightAway(el, directive, component) {
        const method = directive.method || '$refresh'

        component.addAction(new MethodAction(method, directive.params, el))
    },

    attachModelListener(el, directive, component) {
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
                const value = el.valueFromInput()

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
