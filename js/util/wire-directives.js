export function wireDirectives(el) {
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
        return !this.has(type)
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

    setEventContext(context) {
        this.eventContext = context
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

    durationOr(defaultDuration) {
        let durationInMilliSeconds
        const durationInMilliSecondsString = this.modifiers.find(mod => mod.match(/([0-9]+)ms/))
        const durationInSecondsString = this.modifiers.find(mod => mod.match(/([0-9]+)s/))

        if (durationInMilliSecondsString) {
            durationInMilliSeconds = Number(durationInMilliSecondsString.replace('ms', ''))
        } else if (durationInSecondsString) {
            durationInMilliSeconds = Number(durationInSecondsString.replace('s', '')) * 1000
        }

        return durationInMilliSeconds || defaultDuration
    }

    parseOutMethodAndParams(rawMethod) {
        let method = rawMethod
        let params = []
        const methodAndParamString = method.match(/(.*?)\((.*)\)/)

        if (methodAndParamString) {
            // This "$event" is for use inside the livewire event handler.
            const $event = this.eventContext
            method = methodAndParamString[1]
            // use a function that returns it's arguments to parse and eval all params
            params = eval(`(function () {
                for (var l=arguments.length, p=new Array(l), k=0; k<l; k++) {
                    p[k] = arguments[k];
                }
                return [].concat(p);
            })(${methodAndParamString[2]})`)
        }

        return { method, params }
    }

    cardinalDirectionOr(fallback = 'right') {
        if (this.modifiers.includes('up')) return 'up'
        if (this.modifiers.includes('down')) return 'down'
        if (this.modifiers.includes('left')) return 'left'
        if (this.modifiers.includes('right')) return 'right'
        return fallback
    }
}
