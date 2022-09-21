import { on } from '@synthetic/index'
import { directives } from "../directives"

export default function () {
    return;

    on('element.init', (el, component) => {
        let elDirectives = directives(el)

        if (elDirectives.missing('navigate')) return

        let directive = elDirectives.get('navigate')

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
