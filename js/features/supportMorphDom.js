import { morph } from '@/morph'
import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    let html = effects.html
    if (! html) return

    // Wrapping this in a double queueMicrotask. The first one puts it after all
    // other "effect" hooks, and the second one puts it after all reactive
    // Alpine effects (that are processed via flushJobs in scheduler).
    queueMicrotask(() => {
        queueMicrotask(() => {
            morph(component, component.el, html)
        })
    })
})
