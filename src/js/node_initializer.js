import { debounce, kebabCase } from './util'
import ModelAction from './action/model'
import MethodAction from './action/method'
import LivewireElement from './dom/element'

export default class {
    initialize(el, component) {
        // Parse out "direcives", "modifiers", and "value" from livewire attributes.
        el.directives.all().forEach(directive => {
            switch (directive.type) {
                case 'loading-class':
                    this.registerElementForLoading(el, directive, component)
                    break;

                case 'poll':
                    this.fireActionOnInterval(el, directive, component)
                    break;

                case 'model':
                    this.attachModelListener(el, directive, component)
                    break;

                default:
                    this.attachDomListener(el, directive, component)
                    break;
            }
        })
    }

    registerElementForLoading(el, directive, component) {
        const refName = el.directives.get('loading-target')
            && el.directives.get('loading-target').value

        component.addLoadingEl(
            el,
            directive.value,
            refName,
            directive.modifiers.includes('remove')
        )
    }

    fireActionOnInterval(el, directive, component) {
        const method = directive.method || '$refresh'

        setInterval(() => {
            component.addAction(new MethodAction(method, directive.params, el))
        }, directive.durationOr(500));
    }

    attachModelListener(el, directive, component) {
        const isLive = ! directive.modifiers.includes('lazy')
        const debounceOrDont = isLive ? debounce : fn => fn

        el.addEventListener('input', debounceOrDont(e => {
            const model = directive.value
            const el = new LivewireElement(e.target)
            const value = el.valueFromInputOrCheckbox()

            if (isLive) {
                component.addAction(new ModelAction(model, value, el))
            } else {
                component.queueSyncInput(model, value)
            }
        }, 150))
    }

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
    }

    attachListener(el, directive, component, callback) {
        el.addEventListener(directive.type, (e => {
            if (callback && callback(e) !== false) {
                return
            }

            const el = new LivewireElement(e.target)

            // This is outside the conditional below so "wire:click.prevent" without
            // a value still prevents default.
            this.preventAndStop(e, directive.modifiers)

            if (directive.value) {
                component.addAction(new MethodAction(directive.method, directive.params, el))
            }
        }))
    }

    preventAndStop(event, modifiers) {
        modifiers.includes('prevent') && event.preventDefault()

        modifiers.includes('stop') && event.stopPropagation()
    }
}
