import { directive } from '@/directives'
import { handleFileUpload } from '@/features/supportFileUploads'
import { closestComponent } from '@/store'
import { dataGet, dataSet } from '@/utils'
import Alpine from 'alpinejs'

directive('model', ({ el, directive, component, cleanup }) => {
    let { expression, modifiers } = directive

    if (! expression) {
        return console.warn('Livewire: [wire:model] is missing a value.', el)
    }

    if (componentIsMissingProperty(component, expression)) {
        return console.warn('Livewire: [wire:model="'+expression+'"] property does not exist on component: ['+component.name+']', el)
    }

    // Handle file uploads differently...
    if (el.type && el.type.toLowerCase() === 'file') {
        return handleFileUpload(el, expression, component, cleanup)
    }

    let isLive = modifiers.includes('live')
    let isLazy = modifiers.includes('lazy') || modifiers.includes('change')
    let onBlur = modifiers.includes('blur')
    let isDebounced = modifiers.includes('debounce')
    let isInterruptible = modifiers.includes('interruptible')

    // Trigger a network request (only if .live or .lazy is added to wire:model)...
    let update = expression.startsWith('$parent')
        ? () => component.$wire.$parent.$commit(isInterruptible)
        : () => component.$wire.$commit(isInterruptible)

    // If a plain wire:model is added to a text input, debounce the
    // trigerring of network requests.
    let debouncedUpdate = isTextInput(el) && ! isDebounced && isLive
        ? debounce(update, 150)
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
        'lazy', 'defer', 'interruptible'
    ].includes(i))

    if (modifiers.length === 0) return ''

    return '.' + modifiers.join('.')
}

function isTextInput(el) {
    return ['INPUT', 'TEXTAREA'].includes(el.tagName.toUpperCase()) && ! ['checkbox', 'radio'].includes(el.type)
}

function isDirty(subject, dirty) {
    // Check for exact match: wire:model="bob" in ['bob']
    if (dirty.includes(subject)) return true

    // Check case of parent: wire:model="bob.1" in ['bob']
    return dirty.some(i => subject.startsWith(i))
}

function componentIsMissingProperty(component, property) {
    // Breadcrumbs like: foo.0.bar
    let segments = property.split('.')

    // Filter out numeric (array) indices
    // This is to skip validation of properties like: foo.0.bar
    // Because JS can't validate that, and we can't use dataGet because
    // this property doesn't actually exist yet.
    let nonArraySegments = segments.filter(segment => {
        return ! isArrayIndex(segment)
    })

    let fullPropertyName = nonArraySegments.join('.')

    return ! propertyExistsDeep(component.reactive, fullPropertyName)
}

function isArrayIndex(subject) {
    return Array.isArray(subject) || (
        typeof subject === 'string'
        && subject.match(/^[0-9]+$/)
    )
}

function propertyExistsDeep(object, key) {
    if (! key.includes('.')) {
        // If the key is undefined (not a property that was defined with a default value of "undefined")
        return object[key] !== undefined
    }

    let segment = key.split('.')[0]

    if (object[segment] === undefined) return false

    return propertyExistsDeep(object[segment], key.split('.').slice(1).join('.'))
}

function debounce(func, wait) {
    var timeout
    return function() {
        var context = this, args = arguments
        var later = function() {
            timeout = null
            func.apply(context, args)
        }
        clearTimeout(timeout)
        timeout = setTimeout(later, wait)
    }
}
