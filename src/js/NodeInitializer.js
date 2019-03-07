import debounce from './Debounce.js'
import store from './Store'
const prefix = require('./Prefix.js')()
import { closestByAttribute, getAttribute } from './DomHelpers'
import ElementDirectives from './ElementDirectives';

export default class NodeInitializer {
    constructor(connection) {
        this.connection = connection.init()
    }

    initialize(node) {
        // Make sure it's an ElementNode and not a TextNode or something.
        if (typeof node.hasAttribute !== 'function') return

        // Parse out "direcives", "modifiers", and "value" from livewire attributes.
        const directives = new ElementDirectives(node)

        directives.all().forEach(directive => {
            if (directive.type === 'model') {
                this.attachModelListener(node, directive)
            }

            this.attachDomListener(node, directive)
        })
    }

    attachModelListener(el, directive) {
        el.addEventListener('input', debounce(e => {
            const model = directive.value

            const value = e.target.type === 'checkbox'
                ? e.target.checked
                : e.target.value

            if (directive.modifiers.includes('lazy')) {
                this.componentByEl(e.target).queueSyncInput(model, value)
            } else {
                this.connection.sendSync(model, value, this.componentByEl(e.target))
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
        if (directive.modifiers.includes('min')) {
            var waitTime = Number(
                (directive.modifiers.filter(item => item.match(/.*ms/))[0] || '0ms').match('(.*)ms')[1]
            )
        } else {
            var waitTime = 0
        }

        el.addEventListener(directive.type, (e => {
            if (callback && callback(e) !== false) {
                return
            }

            // This is outside the conditional below so "wire:click.prevent" without
            // a value still prevents default.
            this.preventOrStop(e, directive.modifiers)

            if (directive.value) {
                const { method, params } = this.parseOutMethodAndParams(directive.value)

                if (method === '$emit') {
                    const [eventName, ...otherParams] = params
                    this.connection.sendEvent(eventName, otherParams, this.componentByEl(e.target))
                    return
                }

                this.connection.sendMethod(method, params, this.componentByEl(e.target), e.target.getAttribute(`${prefix}:ref`), waitTime)
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
        return getAttribute(closestByAttribute(el, 'id'), 'id')
    }
}
