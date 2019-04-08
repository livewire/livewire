import ElementDirective from './directive';

const prefix = require('./prefix.js')()

export default class {
    constructor(el) {
        this.el = el
        this.directives = this.extractTypeModifiersAndValue()
    }

    all() {
        return Object.values(this.directives)
    }

    has(type) {
        return Object.keys(this.directives).includes(type)
    }

    missing(type) {
        return ! Object.keys(this.directives).includes(type)
    }

    get(type) {
        return this.directives[type]
    }

    extractTypeModifiersAndValue() {
        let directives = {}

        this.el.getAttributeNames()
            // Filter only the livewire directives.
            .filter(name => name.match(new RegExp(prefix + ':')))
            // Parse out the type, modifiers, and value from it.
            .forEach(name => {
                const [type, ...modifiers] = name.replace(new RegExp(prefix + ':'), '').split('.')

                directives[type] = new ElementDirective(type, modifiers, name, this.el)
            })

        return directives
    }
}
