import Alpine from 'alpinejs'
import { evaluateReactiveExpression } from '../evaluator'

Alpine.interceptInit(el => {
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:bind:')) {
            let { name, value } = el.attributes[i]

            let remainder = name.split('wire:bind')[1]

            let expression = value.trim()

            Alpine.bind(el, {
                ['x-bind' + remainder]() {
                    return evaluateReactiveExpression(el, expression)
                }
            })
        }
    }
})
