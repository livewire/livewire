import { directives } from '../directives'
import { dataGet, dataSet, debounce, debounce as generateDebounce, throttle } from '../utils'
import { on } from './../synthetic/index'
import { closestComponent } from '../lifecycle'
import { deferMutation } from './../data'
import Alpine from 'alpinejs'
import { debounceByComponent } from 'debounce'
import { findComponent } from 'state'

export default function () {
    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('model')) return

        let directive = allDirectives.get('model')

        if (! directive.value) {
            console.warn('Livewire: [wire:model] is missing a value.', el)
            return
        }

        let lazy = directive.modifiers.includes('lazy')

        let modifierTail = getModifierTail(directive.modifiers)

        let live = directive.modifiers.includes('live')

        let update = debounceByComponent(component, (component) => {
            if (! live) return

            component.$wire.$commit()
        }, 250)

        Alpine.bind(el, {
            // "unintrusive" in this case means to not update the value of the input
            // if it is a currently focused text input.
            // ['x-model.unintrusive' + modifierTail]() {
            ['x-model' + modifierTail]() {
                return {
                    get() {
                        return dataGet(closestComponent(el).$wire, directive.value)
                    },
                    set(value) {
                        let component = closestComponent(el)

                        dataSet(component.$wire, directive.value, value)

                        update(component)
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
