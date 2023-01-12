import { directive } from "@/directives"
import { on } from '@synthetic/index'
import Alpine from 'alpinejs'

on('morph.added', (el) => {
    el.__addedByMorph = true
})

directive('transition', (el, directive, { component }) => {
    if (! el.__addedByMorph) return

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
