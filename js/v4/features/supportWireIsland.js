import { directive } from "@/directives"
import interceptor from '@/v4/interceptors/interceptors.js'
import messageBroker from '@/v4/requests/messageBroker.js'
import { closestIslandName } from '@/features/supportIslands.js'

let wireIslands = new WeakMap

interceptor.add(({el, directive, component}) => {
    let name = wireIslands.get(el)?.name ?? closestIslandName(el)

    if (! name) return

    messageBroker.addContext(component, 'islands', name)
})

directive('island', ({ el, directive }) => {
    let name = directive.expression ?? 'default'

    let mode = directive.modifiers.includes('append')
        ? 'append'
        : (directive.modifiers.includes('prepend')
            ? 'prepend'
            : 'replace')

    wireIslands.set(el, {
        name,
        mode,
    })
})
