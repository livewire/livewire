import { setNextActionOrigin } from '@/request'
import { evaluateActionExpression } from '../evaluator'
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
        } else if (el.attributes[i].name.startsWith('wire:sort:group-id')) {
            // This will get read by the wire:sort handler below...
            continue
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

            // Strip .offline from Alpine expression because it only concerns Livewire and trips up Alpine...
            if (directive.modifiers.includes('offline')) {
                attribute = attribute.replace('.offline', '')
            }

            // Strip .queue from Alpine expression because it only concerns Livewire and trips up Alpine...
            if (directive.modifiers.includes('queue')) {
                attribute = attribute.replace('.queue', '')
            }
            // Strip .renderless from Alpine expression because it only concerns Livewire and trips up Alpine...
            if (directive.modifiers.includes('renderless')) {
                attribute = attribute.replace('.renderless', '')
            }

            // Strip .prepend from Alpine expression because it only concerns Livewire and trips up Alpine...
            if (directive.modifiers.includes('prepend')) {
                attribute = attribute.replace('.prepend', '')
            }

            // Strip .append from Alpine expression because it only concerns Livewire and trips up Alpine...
            if (directive.modifiers.includes('append')) {
                attribute = attribute.replace('.append', '')
            }

            let expression = directive.expression

            Alpine.bind(el, {
                [attribute]() {
                    setNextActionOrigin({ el, directive })

                    let params = [this.$item, this.$position]
                    let scope = { $item: this.$item, $position: this.$position }

                    let sortId = el.getAttribute('wire:sort:group-id')

                    if (sortId !== null) {
                        params.push(sortId)
                        scope.$id = sortId
                    }

                    evaluateActionExpression(el, expression, { scope, params })
                }
            })
        }
    }
})
