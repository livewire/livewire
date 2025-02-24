import { overrideMethod } from '@/$wire'
import { on } from '@/hooks'
import Alpine from 'alpinejs'

on('effect', ({ component, effects }) => {
    let js = effects.js
    let xjs = effects.xjs

    if (js) {
        Object.entries(js).forEach(([method, body]) => {
            overrideMethod(component, method, () => {
                Alpine.evaluate(component.el, body, { scope: component.jsActions })
            })
        })
    }

    if (xjs) {
        xjs.forEach(({ expression, params }) => {
            params = Object.values(params)

            Alpine.evaluate(component.el, expression, { scope: component.jsActions, params })
        })
    }
})
