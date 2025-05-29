import { on } from './hooks'

let customDirectiveNames = new Set

export function matchesForLivewireDirective(attributeName) {
    return attributeName.match(new RegExp('wire:'))
}

export function extractDirective(el, name) {
    let [value, ...modifiers] = name.replace(new RegExp('wire:'), '').split('.')

    return new Directive(value, modifiers, name, el)
}

export function directive(name, callback) {
    // Prevent the same directive from registering multiple initialization listeners...
    if (customDirectiveNames.has(name)) return

    customDirectiveNames.add(name)

    on('directive.init', ({ el, component, directive, cleanup }) => {
        if (directive.value === name) {
            callback({
                el, directive, component, $wire: component.$wire, cleanup
            })
        }
    })
}

export function globalDirective(name, callback) {
    // Prevent the same directive from registering multiple initialization listeners...
    if (customDirectiveNames.has(name)) return

    customDirectiveNames.add(name)

    on('directive.global.init', ({ el, directive, cleanup }) => {
        if (directive.value === name) {
            callback({ el, directive, cleanup })
        }
    })
}

export function getDirectives(el) {
    return new DirectiveManager(el)
}

export function customDirectiveHasBeenRegistered(name) {
    return customDirectiveNames.has(name)
}

class DirectiveManager {
    constructor(el) {
        this.el = el
        this.directives = this.extractTypeModifiersAndValue()
    }

    all() {
        return this.directives
    }

    has(value) {
        return this.directives.map(directive => directive.value).includes(value)
    }

    missing(value) {
        return ! this.has(value)
    }

    get(value) {
        return this.directives.find(directive => directive.value === value)
    }

    extractTypeModifiersAndValue() {
        return Array.from(this.el.getAttributeNames()
            // Filter only the livewire directives.
            .filter(name => matchesForLivewireDirective(name))
            // Parse out the type, modifiers, and value from it.
            .map(name => extractDirective(this.el, name)))
    }
}

export class Directive {
    constructor(value, modifiers, rawName, el) {
        this.rawName = this.raw = rawName
        this.el = el
        this.eventContext

        this.value = value
        this.modifiers = modifiers
        this.expression = this.el.getAttribute(this.rawName)
    }

    get method() {
        const  methods  = this.parseOutMethodsAndParams(this.expression)

        return methods[0].method;
    }

    get methods() {
        return this.parseOutMethodsAndParams(this.expression);
    }

    get params() {
        const  methods  = this.parseOutMethodsAndParams(this.expression)

        return methods[0].params;
    }

    parseOutMethodsAndParams(rawMethod) {
        // regex selects first method it encounters including possible commas indicating more methods
        let methodRegex = /(.*?)\((.*?\)?)\) *(,*) */s;

        let method = rawMethod
        let params = []
        let methodAndParamString = method.match(methodRegex)

        let methods = [];
        let slicedLength = 0;
        // If there's a method and params, we need to parse them out.
        // If there's multiple methods, parse them one at a time.
        while (methodAndParamString) {
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

            methods.push({ method, params })
            slicedLength += methodAndParamString[0].length;

            // remove all parsed functions from rawMethod string.
            methodAndParamString = rawMethod.slice(slicedLength).match(methodRegex)
        }

        if(methods.length === 0) {
            methods.push({ method, params })
        }

        return methods;
    }
}
