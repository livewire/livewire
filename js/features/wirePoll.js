import { on } from './../../../synthetic/js/index'
import { directives } from "../directives"

export default function () {
    on('element.init', (el, component) => {
        let elDirectives = directives(el)

        if (elDirectives.missing('poll')) return

        let directive = elDirectives.get('poll')

        Alpine.bind(el, {
            'x-init'() {
                component.$wire.$poll(() => {
                    directive.value
                        ? Alpine.evaluate(el, '$wire.'+directive.value)
                        : Alpine.evaluate(el, '$wire.$commit()')
                })
            },
        })
    })
}
