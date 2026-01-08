import Alpine from 'alpinejs'
import { evaluateActionExpression } from '../evaluator'

Alpine.interceptInit(el => {
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:text')) {
            let { name, value } = el.attributes[i]

            let modifierString = name.split('wire:text')[1]

            let expression = value.trim()

            Alpine.bind(el, {
                ['x-text' + modifierString]() {
                    return evaluateActionExpression(el, expression)
                }
            })
        }
    }
})
