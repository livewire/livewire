import { directives } from "../directives"
import { on } from './../synthetic/index'
import Alpine from 'alpinejs'

export default function () {
    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('init')) return

        let directive = allDirectives.get('init')

        const method = directive.value ? directive.method : '$refresh'

        Alpine.evaluate(el, '$wire.'+method)
    })
}
