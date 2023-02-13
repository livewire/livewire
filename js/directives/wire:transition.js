import { directive } from "@/directives"
import { WeakBag } from "@/utils"
import { on } from '@synthetic/index'
import Alpine from 'alpinejs'

on('morph.added', (el) => {
    el.__addedByMorph = true
})

let removalCallbacks = new WeakBag

on('morph.removing', (el, skip) => {
    removalCallbacks.each(el, callback => callback(skip))
})

directive('transition', (el, directive, { component }) => {
    el.__hasLivewireTransition = true

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

    removalCallbacks.add(el, skip => {
        skip()

        el.addEventListener('transitionend', () => {
            el.remove()
        })

        visibility.state = false
    })
})
