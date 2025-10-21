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
        this.wire

        this.value = value
        this.modifiers = modifiers
        this.expression = this.el.getAttribute(this.rawName)
    }

    get method() {
        const  methods  = this.parseOutMethodsAndParams(this.expression)

        return methods[0].method
    }

    get methods() {
        return this.parseOutMethodsAndParams(this.expression)
    }

    get params() {
        const  methods  = this.parseOutMethodsAndParams(this.expression)

        return methods[0].params
    }

    parseOutMethodsAndParams(rawMethod) {
        let methods = []

        // Split methods string into individual methods and their parameters
        let parsedMethods = this.splitAndParseMethods(rawMethod)

        // Evaluate parameters for each method and build the methods array
        for (let { method, paramString } of parsedMethods) {
            let params = []

            if (paramString.length > 0) {
                function argumentsToArray() {
                    for (var l=arguments.length, p=new Array(l), k=0; k<l; k++) {
                        p[k] = arguments[k]
                    }
                    return [].concat(p)
                }

                try {
                    params = Alpine.evaluate(
                        document,
                        'argumentsToArray(' + paramString + ')',
                        {
                            scope: { argumentsToArray },
                        },
                    )
                } catch (error) {
                    console.warn('Failed to parse parameters:', paramString, error)
                    params = []
                }
            }

            methods.push({ method, params })
        }

        return methods
    }

    splitAndParseMethods(methodExpression) {
        let methods = []
        let current = ''
        let parenCount = 0
        let inString = false
        let stringChar = null

        let trimmedExpression = methodExpression.trim()

        // Parse the expression character by character, splitting on commas only at the top level
        // This handles nested parentheses and strings correctly: foo(bar(1,2), 'hello, world')
        for (let i = 0; i < trimmedExpression.length; i++) {
            let char = trimmedExpression[i]

            if (!inString) {
                if (char === '"' || char === "'") {
                    inString = true
                    stringChar = char
                    current += char
                } else if (char === '(') {
                    parenCount++
                    current += char
                } else if (char === ')') {
                    parenCount--
                    current += char
                } else if (char === ',' && parenCount === 0) {
                    // Found a comma at the top level - parse and add this method
                    methods.push(this.parseMethodCall(current.trim()))
                    current = ''
                } else {
                    current += char
                }
            } else {
                // Inside a string - only exit on matching quote that's not escaped
                if (char === stringChar && trimmedExpression[i-1] !== '\\') {
                    inString = false
                    stringChar = null
                }
                current += char
            }
        }

        // Add the last method if there's anything left
        if (current.trim().length > 0) {
            methods.push(this.parseMethodCall(current.trim()))
        }

        return methods
    }

    parseMethodCall(methodString) {
        // Find the method name - everything before the first opening parenthesis
        let methodMatch = methodString.match(/^([^(]+)\(/)
        if (!methodMatch) {
            // No parentheses found, treat as method with no params
            return {
                method: methodString.trim(),
                paramString: ''
            }
        }

        let method = methodMatch[1].trim()
        let paramStart = methodMatch[0].length - 1 // Position of opening parenthesis

        // Since splitAndParseMethods already validated balanced parentheses,
        // we can safely find the last closing parenthesis in the string
        let lastParenIndex = methodString.lastIndexOf(')')
        if (lastParenIndex === -1) {
            throw new Error(`Missing closing parenthesis for method "${method}"`)
        }

        let paramString = methodString.slice(paramStart + 1, lastParenIndex).trim()

        return {
            method,
            paramString
        }
    }
}
