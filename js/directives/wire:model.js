import { debounceByComponent } from '@/debounce'
import { directive } from '@/directives'
import { on } from '@/events'
import { handleFileUpload } from '@/features/supportFileUploads'
import { dataGet, dataSet } from '@/utils'
import Alpine from 'alpinejs'

function debounce(func, wait) {
    var timeout;
    return function() {
      var context = this, args = arguments;
      var later = function() {
        timeout = null;
        func.apply(context, args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

directive('model', (el, { expression, modifiers }, { component, cleanup }) => {
    if (! expression) {
        return console.warn('Livewire: [wire:model] is missing a value.', el)
    }

    if (componentIsMissingProperty(component, expression)) {
        return console.warn('Livewire: [wire:model="'+expression+'"] property does not exist.', el)
    }

    // Handle file uploads differently...
    if (el.type && el.type.toLowerCase() === 'file') {
        return handleFileUpload(el, expression, component, cleanup)
    }

    let isLive = modifiers.includes('live')
    let isLazy = modifiers.includes('lazy')
    let onBlur = modifiers.includes('blur')
    let isDebounced = modifiers.includes('debounce')

    // Trigger a network request (only if .live or .lazy is added to wire:model)...
    let update = () => component.$wire.$commit()

    // If a plain wire:model is added to a text input, debounce the
    // trigerring of network requests.
    let debouncedUpdate = isTextInput(el) && ! isDebounced && isLive
        ? debounce(update, 150)
        // ? debounceByComponent(component, update, 150)
        : update

    Alpine.bind(el, {
        ['@change']() {
            isLazy && update()
        },
        ['@blur']() {
            onBlur && update()
        },
        ['x-model' + getModifierTail(modifiers)]() {
            return {
                get() {
                    return dataGet(component.$wire, expression)
                },
                set(value) {
                    dataSet(component.$wire, expression, value)

                    isLive && (! isLazy) && (! onBlur) && debouncedUpdate()
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

function isDirty(subject, dirty) {
    // Check for exact match: wire:model="bob" in ['bob']
    if (dirty.includes(subject)) return true

    // Check case of parent: wire:model="bob.1" in ['bob']
    return dirty.some(i => subject.startsWith(i))
}

function componentIsMissingProperty(component, property) {
    let baseProperty = property.split('.')[0]

    return ! Object.keys(component.canonical).includes(baseProperty)
}
