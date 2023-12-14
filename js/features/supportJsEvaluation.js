import { overrideMethod } from '@/$wire'
import { on } from '@/hooks'
import Alpine from 'alpinejs'

on('effects', (component, effects) => {
    let js = effects.js
    let xjs = effects.xjs

    if (js) {
        Object.entries(js).forEach(([method, body]) => {
            overrideMethod(component, method, () => {
                Alpine.evaluate(component.el, body)
            })
        })
    }

    if (xjs) {
        xjs.forEach(expression => {
            Alpine.evaluate(component.el, expression)
        })
    }
})

