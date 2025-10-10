import { setNextActionOrigin } from '@/request'
import { evaluateActionExpressionWithoutComponentScope } from '../evaluator'
import Alpine from 'alpinejs'
import { extractDirective } from '@/directives'

Alpine.interceptInit(el => {
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:sort:item')) {
            let directive = extractDirective(el, el.attributes[i].name)

            let modifierString = directive.modifiers.join('.')

            let expression = directive.expression

            Alpine.bind(el, {
                ['x-sort:item' + modifierString]() {
                    return expression
                }
            })
        } else if (el.attributes[i].name.startsWith('wire:sort:group')) {
            // This will get picked up by Alpine's x-sort source...
            return
        } else if (el.attributes[i].name.startsWith('wire:sort')) {
            let directive = extractDirective(el, el.attributes[i].name)

            let attribute = directive.rawName.replace('wire:', 'x-')

            // Strip .async from Alpine expression because it only concerns Livewire and trips up Alpine...
            if (directive.modifiers.includes('async')) {
                attribute = attribute.replace('.async', '')
            }

            // Strip .renderless from Alpine expression because it only concerns Livewire and trips up Alpine...
            if (directive.modifiers.includes('renderless')) {
                attribute = attribute.replace('.renderless', '')
            }

            let expression = directive.expression

            Alpine.bind(el, {
                [attribute]() {
                    setNextActionOrigin({ el, directive })

                    return evaluateActionExpressionWithoutComponentScope(el, expression, { scope: {
                        $item: this.$item,
                        $position: this.$position,
                    } })
                }
            })
        }
    }
})