import Alpine from 'alpinejs'
import { extractDirective } from '@/directives'
import { evaluateActionExpression } from '@/evaluator'
import { findComponentByEl } from '@/store'

Alpine.interceptInit(el => {
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:intersect')) {
            let { name, value } = el.attributes[i]

            let directive = extractDirective(el, name)

            let modifierString = name.split('wire:intersect')[1]

            let expression = value.trim()

            Alpine.bind(el, {
                ['x-intersect' + modifierString](e) {
                    directive.eventContext = e

                    let component = findComponentByEl(el, false)

                    if (! component) return

                    component.addActionContext({
                        el,
                        directive,
                    })

                    evaluateActionExpression(el, expression)
                }
            })
        }
    }
})
