import { evaluateExpression } from '../evaluator'
import { findComponentByEl } from '@/store'
import { overrideMethod } from '@/$wire'
import { interceptMessage } from '@/request'
import { on } from '@/hooks'
import Alpine from 'alpinejs'

Alpine.magic('js', el => {
    let component = findComponentByEl(el)

    return component.$wire.js
})

on('component.initialized', ({ component }) => {
    evaluateJsEffects(component, component.effects)
})

interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorphed }) => {
        onMorphed(() => evaluateJsEffects(message.component, payload.effects))
    })
})

function evaluateJsEffects(component, effects) {
    let js = effects.js
    let xjs = effects.xjs

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
}
