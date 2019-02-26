const prefix = require('./prefix.js')()

export default class {
    constructor(el) {
        this.el = el
        this.modifiersAndValuesByDirective = this.extractDirectivesModifiersAndValues(el)
    }

    has(directive) {
        return Object.keys(this.modifiersAndValuesByDirective).includes(directive)
    }

    get(directive) {
        return this.modifiersAndValuesByDirective[directive]
    }

    extractDirectivesModifiersAndValues(el) {
        let directives = {}

        el.getAttributeNames()
            // Filter only the livewire directives.
            .filter(name => name.match(new RegExp(prefix + ':')))
            // Parse out the event, modifiers, and value from it.
            .forEach(name => {
                let directive, modifiers
                [directive, ...modifiers] = name.replace(new RegExp(prefix + ':'), '').split('.')

                directives[directive] = { modifiers, value: el.getAttribute(name) }
            })

        return directives
    }
}
