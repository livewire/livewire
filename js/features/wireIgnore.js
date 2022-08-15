import { directives } from "../directives"
import { on } from "../events"

export default function () {
    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('ignore')) return

        let directive = allDirectives.get('ignore')

        if (directive.modifiers.includes('self')) {
            el.__livewire_ignore_self = true
        } else {
            el.__livewire_ignore = true
        }
    })
}
