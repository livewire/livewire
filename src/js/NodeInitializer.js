import debounce from './Debounce.js'
import store from './Store'
import LivewireElement from './LivewireElement';
import MethodAction from './MethodAction.js';
import ModelAction from './ModelAction.js';
import EventAction from './EventAction.js';
const prefix = require('./Prefix.js')()

export default class {
    initialize(el, component) {
        // Parse out "direcives", "modifiers", and "value" from livewire attributes.
        el.directives.all().forEach(directive => {
            switch (directive.type) {
                case 'loading-class':
                    this.registerElementForLoading(el, directive, component)
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
        const refName = el.directives.get('loading-target') ? el.directives.get('loading-target').value : null

        component.addLoadingEl(
            el,
            directive.value,
            refName,
            directive.modifiers.includes('remove')
        )
    }

    attachModelListener(el, directive, component) {
        el.addEventListener('input', debounce(e => {
            const model = directive.value
            const el = new LivewireElement(e.target)
            const value = el.valueFromInputOrCheckbox()

            if (directive.modifiers.includes('live')) {
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
                        || directive.modifiers.includes(e.key.split(/[_\s]/).join("-").toLowerCase()))
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
                const { method, params } = this.parseOutMethodAndParams(directive.value)

                if (method === '$emit') {
                    const [eventName, ...otherParams] = params

                    component.parent.addAction(new EventAction(eventName, otherParams, component.id, el))
                    return
                }

                component.addAction(new MethodAction(method, params, el))
            }
        }))
    }

    parseOutMethodAndParams(rawMethod) {
        let params = []
        let method = rawMethod

        if (method.match(/(.*)\((.*)\)/)) {
            const matches = method.match(/(.*)\((.*)\)/)
            method = matches[1]
            params = matches[2].split(', ').map(param => {
                if (eval('typeof ' + param) === 'undefined') {
                    return document.querySelector(`[${prefix}\\:model="` + param + '"]').value
                }

                return eval(param)
            })
        }

        return { method, params }
    }

    preventAndStop(event, modifiers) {
        modifiers.includes('prevent') && event.preventDefault()

        modifiers.includes('stop') && event.stopPropagation()
    }

    componentByEl(el) {
        return store.findComponent(this.getComponentIdFromEl(el))
    }

    getComponentIdFromEl(el) {
        return el.closestByAttribute('id').getAttribute('id')
    }
}
