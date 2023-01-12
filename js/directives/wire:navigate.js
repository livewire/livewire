import { getDirectives } from "@/directives"
import { on } from '@synthetic/index'

// on('element.init', (el, component) => {
//     let directives = getDirectives(el)

//     if (directives.missing('navigate')) return

//     let directive = directives.get('navigate')

//     Alpine.bind(el, {
//         'x-init'() {
//             component.$wire.$poll(() => {
//                 directive.value
//                     ? Alpine.evaluate(el, '$wire.'+directive.value)
//                     : Alpine.evaluate(el, '$wire.$commit()')
//             })
//         },
//     })
// })
