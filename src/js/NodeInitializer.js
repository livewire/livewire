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
            switch (directive.type) {
                case 'loading-class':
                    setTimeout(() => {
                        this.registerElementForLoading(el, directive)
                        // Sorry for the setTimeout
                    }, 500)
                    break;

                case 'model':
                    this.attachModelListener(el, directive)
                    break;

                default:
                    this.attachDomListener(el, directive)
                    break;
            }
        })
    }

    registerElementForLoading(el, directive) {
        // "this.componentByEl" is broken because the node we have to work with
        // doesn't have a component parent for some reason yet.
        // this.componentByEl(el).addLoadinnigEl(
        //     el,
        //     directive.value,
        //     el.directives.get('loading-target'),
        //     directive.modifiers.includes('remove')
        // )
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
            this.preventAndStop(e, directive.modifiers)

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
                )
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
