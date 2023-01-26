import { directive, getDirectives } from "@/directives"
import { on } from '@synthetic/index'
import Alpine from 'alpinejs'

directive('poll', (el, directive, { component, cleanup }) => {
    console.log(component.$wire)

    return;

    component.$wire.$poll(() => {
        directive.value
            ? Alpine.evaluate(el, '$wire.'+directive.value)
            : Alpine.evaluate(el, '$wire.$commit()')
    })

    cleanup(() => {
        //
    })
})
// on('element.init', (el, component) => {
//     let directives = getDirectives(el)

//     if (directives.missing('poll')) return

//     let directive = directives.get('poll')


// })
