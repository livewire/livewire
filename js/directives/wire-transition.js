import { directive } from "@/directives"
import { on } from '@/hooks'
import Alpine from 'alpinejs'

on('morph.added', ({ el }) => {
    el.__addedByMorph = true
})

directive('transition', ({ el, directive, component, cleanup }) => {
    // Support using wire:transition with wire:show as well...
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:show')) {
            Alpine.bind(el, {
                [directive.rawName.replace('wire:transition', 'x-transition')]: directive.expression,
            })

            return
        }
    }

    let visibility = Alpine.reactive({ state: el.__addedByMorph ? false : true })

    // We're going to control the element's transition with Alpine transitions...
    Alpine.bind(el, {
        [directive.rawName.replace('wire:', 'x-')]: '',
        'x-show'() { return visibility.state },
    })

    // If it's not the initial page load, transition the element in...
    el.__addedByMorph && setTimeout(() => visibility.state = true)

    let cleanups = []

    cleanups.push(on('morph.removing', ({ el, skip }) => {
        // Here we interupt morphdom from removing an element...
        skip()

        // When the transition ends...
        el.addEventListener('transitionend', () => {
            // We can actually remove the element and all the listeners along with it...
            el.remove()
        })

        // Now we can trigger a transition:
        visibility.state = false

        cleanups.push(on('morph', ({ component: morphComponent }) => {
            if (morphComponent !== component) return

            // While this element is transitioning out, a new morph is about to occur.
            // Let's expidite this one and clean it up so it doesn't interfere...
            el.remove()

            cleanups.forEach(i => i())
        }))
    }))

    cleanup(() => cleanups.forEach(i => i()))
})
