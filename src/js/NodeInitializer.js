import debounce from './Debounce.js'
import store from './Store'
import ElementDirectives from './ElementDirectives';
import LivewireElement from './LivewireElement';
const prefix = require('./Prefix.js')()

export default class {
    constructor(connection) {
        this.connection = connection.init()
    }

    initialize(el) {
        // Parse out "direcives", "modifiers", and "value" from livewire attributes.
        el.directives.all().forEach(directive => {
            if (directive.type === 'model') {
                this.attachModelListener(el, directive)
            } else {
                this.attachDomListener(el, directive)
            }
        })
    }

    attachModelListener(el, directive) {
        el.addEventListener('input', debounce(e => {
            const model = directive.value
            const el = new LivewireElement(e.target)
            const value = el.valueFromInputOrCheckbox()

            if (directive.modifiers.includes('lazy')) {
                this.componentByEl(el).queueSyncInput(model, value)
            } else {
                this.connection.sendModelSync(model, value, this.componentByEl(el))
            }
        }, 150))
    }

    attachDomListener(el, directive) {
        switch (directive.type) {
            case 'keydown':
                this.attachListener(el, directive, (e) => {
                    // Only handle listener if no, or matching key modifiers are passed.
                    return ! (directive.modifiers.length === 0
                        || directive.modifiers.includes(e.key.split(/[_\s]/).join("-").toLowerCase()))
                })
                break;
            default:
                this.attachListener(el, directive)
                break;
        }
    }

    attachListener(el, directive, callback) {
        el.addEventListener(directive.type, (e => {
            if (callback && callback(e) !== false) {
                return
            }

            const el = new LivewireElement(e.target)

            // This is outside the conditional below so "wire:click.prevent" without
            // a value still prevents default.
            this.preventOrStop(e, directive.modifiers)

            if (directive.value) {
                const component = this.componentByEl(el)
                const { method, params } = this.parseOutMethodAndParams(directive.value)

                if (method === '$emit') {
                    const [eventName, ...otherParams] = params
                    this.connection.sendEvent(eventName, otherParams, component)
                    return
                }

                this.connection.sendMethod(
                    method,
                    params,
                    component,
                    el.getAttribute('ref'),
                    this.extractMinWaitModifier(directive)
                )
            }
        }))
    }

    extractMinWaitModifier(directive) {
        // If there is a ".min" modifier
        return directive.modifiers.includes('min')
            // Extract a subsequent .Xms modifier
            ? Number(
                (directive.modifiers.filter(item => item.match(/.*ms/))[0] || '0ms').match('(.*)ms')[1]
            ): 0
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

    preventOrStop(event, modifiers) {
        if (modifiers.includes('prevent')) {
            event.preventDefault()
        }

        if (modifiers.includes('stop')) {
            event.stopPropagation()
        }
    }

    componentByEl(el) {
        return store.componentsById[this.getComponentIdFromEl(el)]
    }

    getComponentIdFromEl(el) {
        return el.closestByAttribute('id').getAttribute('id')
    }
}
