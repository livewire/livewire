import { directives as getDirectives } from '../directives'
import { dataGet, dataSet, debounce, debounce as generateDebounce, throttle } from '../utils'
import { on } from './../synthetic/index'
import { closestComponent } from '../lifecycle'
import { deferMutation } from './../data'
import Alpine from 'alpinejs'
import { debounceByComponent } from 'debounce'
import { findComponent } from 'state'

export default function () {
    on('element.init', (el, component) => {
        let directives = getDirectives(el)

        if (directives.missing('model')) return

        let directive = directives.get('model')

        if (! directive.value) {
            return console.warn('Livewire: [wire:model] is missing a value.', el)
        }

        let isLive = directive.modifiers.includes('live')
        let isLazy = directive.modifiers.includes('lazy')
        let isDebounced = directive.modifiers.includes('debounce')

        // Trigger a network request (only if .live or .lazy is added to wire:model)...
        let update = () => component.$wire.$commit()

        // If a plain wire:model is added to a text input, debounce the
        // trigerring of network requests.
        let debouncedUpdate = isTextInput(el) && ! isDebounced && isLive
            ? debounceByComponent(component, update, 150)
            : update

        Alpine.bind(el, {
            ['@change']() {
                isLazy && isTextInput(el) && update()
            },
            // "unintrusive" in this case means to not update the value of the input
            // if it is a currently focused text input.
            // ['x-model.unintrusive' + modifierTail]() {
            ['x-model.unintrusive' + getModifierTail(directive.modifiers)]() {
                return {
                    get() {
                        return dataGet(component.$wire, directive.value)
                    },
                    set(value) {
                        dataSet(component.$wire, directive.value, value)

                        isLive && debouncedUpdate()
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

function isTextInput(el) {
    return (
        ['INPUT', 'TEXTAREA'].includes(el.tagName.toUpperCase()) &&
        !['checkbox', 'radio'].includes(el.type)
    )
}
