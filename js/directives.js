import { on } from './hooks'
import { extractMethodsAndParamsFromRawExpression } from './utils'

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
        this.wire

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
        return extractMethodsAndParamsFromRawExpression(rawMethod)
    }
}
