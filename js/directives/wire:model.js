import { debounceByComponent } from '@/debounce'
import { directive } from '@/directives'
import { on } from '@/events'
import { handleFileUpload } from '@/features/supportFileUploads'
import { dataGet, dataSet } from '@/utils'
import Alpine from 'alpinejs'

directive('model', (el, { expression, modifiers }, { component, cleanup }) => {
    if (! expression) {
        return console.warn('Livewire: [wire:model] is missing a value.', el)
    }

    // Handle file uploads differently...
    if (el.type.toLowerCase() === 'file') {
        return handleFileUpload(el, expression, component, cleanup)
    }

    forceUpdateOnDirty(component, el, expression, cleanup)

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

function forceUpdateOnDirty(component, el, expression, cleanup) {
    let off = on('request', (iComponent) => {
        if (iComponent !== component) return

        return () => {
            let dirty = component.effects.dirty

            if (! dirty) return

            if (isDirty(expression, dirty)) {
                el._x_forceModelUpdate(
                    component.$wire.get(expression, false)
                )
            }
        }
    })

    cleanup(off)
}

function isDirty(subject, dirty) {
    // Check for exact match: wire:model="bob" in ['bob']
    if (dirty.includes(subject)) return true

    // Check case of parent: wire:model="bob.1" in ['bob']
    return dirty.some(i => subject.startsWith(i))
}
