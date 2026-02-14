import { directive } from '@/directives'
import { handleFileUpload } from '@/features/supportFileUploads'
import { findComponentByEl } from '@/store'
import { dataGet, dataSet } from '@/utils'
import { setNextActionMetadata, setNextActionOrigin } from '@/request'
import Alpine from 'alpinejs'

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

    // Split modifiers at .live boundary
    // Modifiers BEFORE .live control client-side (x-model) sync timing
    // Modifiers AFTER .live control network request timing
    let liveIndex = modifiers.indexOf('live')
    let isLive = liveIndex !== -1

    // Backwards compatibility: .lazy without .live implies .change.live
    let hasLazyWithoutLive = modifiers.includes('lazy') && ! isLive
    let shouldSendNetwork = isLive || hasLazyWithoutLive

    let ephemeralModifiers = isLive && ! hasLazyWithoutLive ? modifiers.slice(0, liveIndex) : modifiers.slice()
    let networkModifiers = isLive && ! hasLazyWithoutLive ? modifiers.slice(liveIndex + 1) : []

    // For .lazy backwards compat, trigger network on change
    if (hasLazyWithoutLive) {
        // Remove 'lazy' from ephemeralModifiers for .lazy backwards compat
        ephemeralModifiers = ephemeralModifiers.filter(m => m !== 'lazy')
        networkModifiers.push('change')
    }

    // Add self/deep modifier for event propagation control
    if (! (ephemeralModifiers.includes('deep') || networkModifiers.includes('deep'))) {
        if (! ephemeralModifiers.includes('self')) {
            ephemeralModifiers.push('self')
        }
    }

    // Extract ephemeral trigger modifiers (these control when x-model syncs)
    let ephemeralOnBlur = ephemeralModifiers.includes('blur')
    let ephemeralOnChange = ephemeralModifiers.includes('change') || ephemeralModifiers.includes('lazy')
    let ephemeralOnEnter = ephemeralModifiers.includes('enter')
    let hasEphemeralTriggers = ephemeralOnBlur || ephemeralOnChange || ephemeralOnEnter

    // Extract network trigger modifiers
    let networkOnBlur = networkModifiers.includes('blur')
    let networkOnChange = networkModifiers.includes('change') || networkModifiers.includes('lazy')
    let networkOnEnter = networkModifiers.includes('enter')
    let hasNetworkTriggers = networkOnBlur || networkOnChange || networkOnEnter
    let isDebounced = networkModifiers.includes('debounce')
    let isThrottled = networkModifiers.includes('throttle')

    // Trigger a network request
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

    // Apply debounce/throttle from network modifiers
    if ((shouldSendNetwork && ! hasNetworkTriggers && isRealtimeInput(el)) || isDebounced) {
        debouncedUpdate = debounce(debouncedUpdate, parseModifierDuration(networkModifiers, 'debounce') || 150)
    }

    if (isThrottled) {
        debouncedUpdate = throttle(debouncedUpdate, parseModifierDuration(networkModifiers, 'throttle') || 150)
    }

    let isModelable = expression.startsWith('$parent')

    // For modelable components with ephemeral event triggers (blur, change, enter),
    // x-model modifiers don't work because the component root is a <div> and values
    // flow through x-modelable's reactivity, bypassing DOM events entirely.
    // Instead, we buffer the value in set() and flush when the appropriate event fires.
    let bufferEphemeral = isModelable && hasEphemeralTriggers

    let pendingValue = undefined
    let hasPending = false

    let flushPending = () => {
        if (! hasPending) return

        dataSet(component.$wire, expression, pendingValue)
        hasPending = false
        pendingValue = undefined

        if (shouldSendNetwork && ! hasNetworkTriggers) {
            debouncedUpdate()
        }
    }

    // Build the bindings object
    let bindings = {}

    // Blur/focusout listener
    let wantsBlur = (bufferEphemeral && ephemeralOnBlur) || (shouldSendNetwork && networkOnBlur)

    if (wantsBlur) {
        if (isModelable) {
            // Use focusout instead of blur — blur doesn't bubble from inner inputs.
            bindings['@focusout'] = (e) => {
                // Only trigger when focus leaves the component entirely,
                // not when moving between inputs within the component
                if (el.contains(e.relatedTarget)) return

                flushPending()

                if (shouldSendNetwork && networkOnBlur) update()
            }
        } else {
            bindings['@blur'] = () => update()
        }
    }

    if ((bufferEphemeral && ephemeralOnChange) || (shouldSendNetwork && networkOnChange)) {
        bindings['@change'] = () => {
            flushPending()

            if (shouldSendNetwork && networkOnChange) update()
        }
    }

    if ((bufferEphemeral && ephemeralOnEnter) || (shouldSendNetwork && networkOnEnter)) {
        bindings['@keydown.enter'] = () => {
            flushPending()

            if (shouldSendNetwork && networkOnEnter) update()
        }
    }

    // Strip event-based modifiers from x-model tail for modelable (they don't work on divs)
    let xModelModifiers = bufferEphemeral
        ? ephemeralModifiers.filter(m => ! ['blur', 'change', 'enter'].includes(m))
        : ephemeralModifiers

    let xModelTail = getModifierTail(xModelModifiers)

    bindings['x-model' + xModelTail] = () => ({
        get() {
            // Return pending value so x-modelable doesn't sync the old value back
            return hasPending ? pendingValue : dataGet(component.$wire, expression)
        },
        set(value) {
            if (bufferEphemeral) {
                pendingValue = value
                hasPending = true
            } else {
                dataSet(component.$wire, expression, value)

                if (shouldSendNetwork && ! hasNetworkTriggers) {
                    debouncedUpdate()
                }
            }
        },
    })

    Alpine.bind(el, bindings)
})

function getModifierTail(modifiers) {
    // Filter out Livewire-specific modifiers that shouldn't go to x-model
    // Keep: blur, change, lazy, enter, self, deep, number, boolean, trim, fill
    // Remove: defer, live, debounce, throttle (and their durations)
    modifiers = modifiers.filter(i => ! [
        'defer', 'live'
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