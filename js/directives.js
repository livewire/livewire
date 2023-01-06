import { on } from './synthetic/index'

export function registerDirective(name, callback) {
    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing(name)) return

        let directive = allDirectives.get(name)

        callback(el, directive, component)
    })
}

export function directives(el) {
    return new DirectiveManager(el)
}

class DirectiveManager {
    constructor(el) {
        this.el = el
        this.directives = this.extractTypeModifiersAndValue()
    }

    all() {
        return this.directives
    }

    has(type) {
        return this.directives.map(directive => directive.type).includes(type)
    }

    missing(type) {
        return ! this.has(type)
    }

    get(type) {
        return this.directives.find(directive => directive.type === type)
    }

    extractTypeModifiersAndValue() {
        return Array.from(this.el.getAttributeNames()
            // Filter only the livewire directives.
            .filter(name => name.match(new RegExp('wire:')))
            // Parse out the type, modifiers, and value from it.
            .map(name => {
                const [type, ...modifiers] = name.replace(new RegExp('wire:'), '').split('.')

                return new Directive(type, modifiers, name, this.el)
            }))
    }
}

class Directive {
    constructor(type, modifiers, rawName, el) {
        this.type = type
        this.modifiers = modifiers
        this.rawName = rawName
        this.el = el
        this.eventContext
    }

    get value() {
        return this.el.getAttribute(this.rawName)
    }

    get method() {
        const { method } = this.parseOutMethodAndParams(this.value)

        return method
    }

    get params() {
        const { params } = this.parseOutMethodAndParams(this.value)

        return params
    }

    parseOutMethodAndParams(rawMethod) {
        let method = rawMethod
        let params = []
        const methodAndParamString = method.match(/(.*?)\((.*)\)/s)

        if (methodAndParamString) {
            method = methodAndParamString[1]

            // Use a function that returns it's arguments to parse and eval all params
            // This "$event" is for use inside the livewire event handler.
            let func = new Function('$event', `return (function () {
                for (var l=arguments.length, p=new Array(l), k=0; k<l; k++) {
                    p[k] = arguments[k];
                }
                return [].concat(p);
            })(${methodAndParamString[2]})`)

            params = func(this.eventContext)
        }

        return { method, params }
    }
}
