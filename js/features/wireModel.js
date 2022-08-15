import { directives } from '../directives'
import { dataGet, dataSet, debounce as generateDebounce } from '../utils'
import { on } from '../events'
import { closestComponent } from '../lifecycle'
import { deferMutation } from './../data'

export default function () {
    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('model')) return

        let directive = allDirectives.get('model')

        if (! directive.value) {
            console.warn('Livewire: [wire:model] is missing a value.', el)
            return
        }

        // Handle dirty inputs.
        // on('component.response', (component, response) => {
        //     if (component !== component) return

        //     if (response.effects.dirty) {
        //         if (response.effects.dirty.includes(directive.value)) {
        //             el._x_forceModelUpdate()
        //         }
        //     }
        // })

        let lazy = directive.modifiers.includes('lazy')

        let modifierTail = getModifierTail(directive.modifiers)

        Alpine.bind(el, {
            // "unintrusive" in this case means to not update the value of the input
            // if it is a currently focused text input.
            ['@change']() {
                if (lazy) {

                }
            },
            ['x-model.unintrusive' + modifierTail]() {
                return {
                    get() {
                        return dataGet(closestComponent(el).$wire, directive.value)
                    },
                    set(value) {
                        dataSet(closestComponent(el).$wire, directive.value, value)
                    },
                }
            }
        })
    })
}

function getModifierTail(modifiers) {
    modifiers = modifiers.filter(i => ! [
        'lazy', 'defer'
    ].includes(i))

    if (modifiers.length === 0) return ''

    return '.' + modifiers.join('.')
}
