import { morph } from '@/morph'
import { on } from '@/hooks'

on('effects', (component, effects) => {
    let html = effects.html
    if (! html) return

    // Doing this so all the state of components in a nested tree has a chance
    // to update on synthetic's end. (mergeSnapshots kinda deal).
    queueMicrotask(() => {
        morph(component, component.el, html)
    })
})
