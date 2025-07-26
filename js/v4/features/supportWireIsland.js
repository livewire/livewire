import { directive } from "@/directives"
import interceptorRegistry from '@/v4/interceptors/interceptorRegistry.js'
import { closestIsland } from '@/features/supportIslands.js'

let wireIslands = new WeakMap

interceptorRegistry.add(({ action, component, request, el, directive }) => {
    if (! el) return

    let island = wireIslands.get(el) ?? closestIsland(component, el)

    if (! island) return

    action.addContext({
        island: {name: island.name, mode: island.mode},
    })
})

directive('island', ({ el, directive, cleanup }) => {
    let name = directive.expression ?? 'default'

    let mode = null

    if (directive.modifiers.includes('append')) {
        mode = 'append'
    } else if (directive.modifiers.includes('prepend')) {
        mode = 'prepend'
    } else if (directive.modifiers.includes('replace')) {
        mode = 'replace'
    }

    wireIslands.set(el, {
        name,
        mode,
    })

    cleanup(() => {
        wireIslands.delete(el)
    })
})
