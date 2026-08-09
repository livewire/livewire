import { evaluateExpression } from '../evaluator'
import { findComponentByEl } from '@/store'
import { overrideMethod } from '@/$wire'
import { on } from '@/hooks'
import Alpine from 'alpinejs'

Alpine.magic('js', el => {
    let component = findComponentByEl(el)

    return component.$wire.js
})

on('effect', ({ component, effects }) => {
    let js
    let xjs

    if (effects.has('js')) {
        js = effects.js
    }

    if (effects.has('xjs')) {
        xjs = effects.xjs
    }

    if (js) {
        Object.entries(js).forEach(([method, body]) => {
            overrideMethod(component, method, () => {
                evaluateExpression(component.el, body)
            })
        })
    }

    if (xjs) {
        xjs.forEach(({ expression, params }) => {
            params = Object.values(params)

            evaluateExpression(component.el, expression, { scope: component.getJsActions(), params })
        })
    }
})
