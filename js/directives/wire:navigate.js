import { directive } from "@/directives"
import Alpine from 'alpinejs'

directive('navigate', (el, directive) => {
    let alpineDirective = directive.rawName.replace('wire:', 'x-')

    Alpine.bind(el, {
        [alpineDirective]: true,
    })
})

