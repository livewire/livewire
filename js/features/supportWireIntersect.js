import Alpine from 'alpinejs'
import { extractDirective } from '@/directives'
import { evaluateActionExpression } from '@/evaluator'

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

                    // @todo: review if there is a better way to get the component...
                    let component = el.closest('[wire\\:id]')?.__livewire

                    component.addActionContext({
                        // type: 'user',
                        el,
                        directive,
                    })

                    evaluateActionExpression(el, expression)
                }
            })
        }
    }
})
