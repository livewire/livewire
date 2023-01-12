import { debounceByComponent } from '@/debounce'
import { directive } from '@/directives'
import { dataGet, dataSet } from '@/utils'
import Alpine from 'alpinejs'

directive('model', (el, { expression, modifiers }, { component }) => {
    if (! expression) {
        return console.warn('Livewire: [wire:model] is missing a value.', el)
    }

    let isLive = modifiers.includes('live')
    let isLazy = modifiers.includes('lazy')
    let isDebounced = modifiers.includes('debounce')

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
        ['x-model.unintrusive' + getModifierTail(modifiers)]() {
            return {
                get() {
                    return dataGet(component.$wire, expression)
                },
                set(value) {
                    dataSet(component.$wire, expression, value)

                    isLive && debouncedUpdate()
                },
            }
        }
    })
})

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
