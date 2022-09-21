import { on } from '@synthetic/index'
import { directives } from "../directives"

export default function () {
    on('morph.added', (el) => {
        el.__addedByMorph = true
    })

    on('element.init', (el, component) => {
        if (! el.__addedByMorph) return

        let elDirectives = directives(el)

        if (elDirectives.missing('transition')) return

        let directive = elDirectives.get('transition')

        let visibility = Alpine.reactive({ state: false })

        Alpine.bind(el, {
            [directive.rawName.replace('wire:', 'x-')]: '',
            'x-show'() {
                return visibility.state
            },
            'x-init'() {
                setTimeout(() => visibility.state = true)
            }
        })
    })
}
