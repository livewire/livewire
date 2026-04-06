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

                    // Alpine's x-sort provides $position as the raw DOM index (e.newIndex
                    // from SortableJS), which counts ALL children including non-sortable
                    // siblings like wire:sort:ignore / x-sort:ignore elements. Recalculate
                    // as the index among only sortable items (wire:sort:item / x-sort:item).
                    let sortableChildren = Array.from(el.children).filter(child =>
                        child.hasAttribute('x-sort:item') || child.hasAttribute('wire:sort:item')
                    )
                    let itemPosition = sortableChildren.findIndex(child => child._x_sort_key === this.$item)
                    let position = itemPosition !== -1 ? itemPosition : this.$position

                    let params = [this.$item, position]
                    let scope = { $item: this.$item, $position: position }

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