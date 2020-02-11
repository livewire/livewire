import ElementDirective from './directive';

export default class {
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

                return new ElementDirective(type, modifiers, name, this.el)
            }))
    }
}
