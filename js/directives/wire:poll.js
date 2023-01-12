import { getDirectives } from "@/directives"
import { on } from '@synthetic/index'
import Alpine from 'alpinejs'

// on('element.init', (el, component) => {
//     let directives = getDirectives(el)

//     if (directives.missing('poll')) return

//     let directive = directives.get('poll')

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
