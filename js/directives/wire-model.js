import { directive } from '@/directives'
import { handleFileUpload } from '@/features/supportFileUploads'
import { findComponentByEl } from '@/store'
import { dataGet, dataSet } from '@/utils'
import { setNextActionMetadata, setNextActionOrigin } from '@/request'
import Alpine from 'alpinejs'
// Action is no longer needed - wire:model uses $commit which creates its own actions

directive('model', ({ el, directive, component, cleanup }) => {
    // @todo: will need to probaby do this further upstream i just don't want to bog down the entire lifecycle right now...
    // this is to support slots properly...
    component = findComponentByEl(el)

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

    if (! modifiers.includes('self') && ! modifiers.includes('deep')) {
        // Make wire:model self-binding by default...
        modifiers.push('self')
    }

    let isLive = modifiers.includes('live')
    let isLazy = modifiers.includes('lazy') || modifiers.includes('change')
    let onBlur = modifiers.includes('blur')
    let isDebounced = modifiers.includes('debounce')
    let isThrottled = modifiers.includes('throttle')

    // Trigger a network request (only if .live or .lazy is added to wire:model)...
    let update = () => {
        setNextActionOrigin({ el, directive })

        if (isLive || isDebounced) {
            setNextActionMetadata({ type: 'model.live' })
        }

        expression.startsWith('$parent')
            ? component.$wire.$parent.$commit()
            : component.$wire.$commit()
    }

    let debouncedUpdate = update

    if ((isLive && isRealtimeInput(el)) || isDebounced) {
        debouncedUpdate = debounce(debouncedUpdate, parseModifierDuration(modifiers, 'debounce') || 150)
    }

    if (isThrottled) {
        debouncedUpdate = throttle(debouncedUpdate, parseModifierDuration(modifiers, 'throttle') || 150)
    }

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

    if (modifiers.includes('debounce')) {
        let index = modifiers.indexOf('debounce')
        let hasDuration = parseModifierDuration(modifiers, 'debounce') !== undefined

        // Delete the subsequent modifier if it's a duration...
        modifiers.splice(index, hasDuration ? 2 : 1)
    }

    if (modifiers.includes('throttle')) {
        let index = modifiers.indexOf('throttle')
        let hasDuration = parseModifierDuration(modifiers, 'throttle') !== undefined

        // Delete the subsequent modifier if it's a duration...
        modifiers.splice(index, hasDuration ? 2 : 1)
    }

    if (modifiers.length === 0) return ''

    return '.' + modifiers.join('.')
}

function isRealtimeInput(el) {
    return (
        ['INPUT', 'TEXTAREA'].includes(el.tagName.toUpperCase()) &&
        !['checkbox', 'radio'].includes(el.type)
    )
        || el.tagName.toUpperCase() === 'UI-SLIDER' // Flux UI
        || el.tagName.toUpperCase() === 'UI-COMPOSER' // Flux UI
}

function isDirty(subject, dirty) {
    // Check for exact match: wire:model="bob" in ['bob']
    if (dirty.includes(subject)) return true

    // Check case of parent: wire:model="bob.1" in ['bob']
    return dirty.some(i => subject.startsWith(i))
}

function componentIsMissingProperty(component, property) {
    if (property.startsWith('$parent')) {
        let parent = findComponentByEl(component.el.parentElement, false)

        if (! parent) return true

        return componentIsMissingProperty(parent, property.slice(7).replace(/^\./, ''))
    }

    // Extract base property, handling both "foo.bar" and "['foo'].bar"
    let match = property.match(/^\[['"]?([^\]'"]+)['"]?\]/) || property.match(/^([^.\[]+)/)
    let baseProperty = match[1]

    return ! Object.keys(component.canonical).includes(baseProperty)
}

function debounce(func, wait) {
    var timeout;

    return function() {
      var context = this, args = arguments;

      var later = function() {
            timeout = null

            func.apply(context, args)
      }

      clearTimeout(timeout)

      timeout = setTimeout(later, wait)
    }
}

function throttle(func, limit) {
    let inThrottle

    return function() {
        let context = this, args = arguments

        if (! inThrottle) {
            func.apply(context, args)

            inThrottle = true

            setTimeout(() => inThrottle = false, limit)
        }
    }
}

function parseModifierDuration(modifiers, key) {
    let index = modifiers.indexOf(key)
    if (index === -1) return undefined

    let nextModifier = modifiers[modifiers.indexOf(key)+1] || 'invalid-wait'
    let duration = nextModifier.split('ms')[0]

    return ! isNaN(duration) ? duration : undefined
}