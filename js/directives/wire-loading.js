import { toggleBooleanStateDirective } from './shared'
import { directive, getDirectives } from "@/directives"
import { on } from '@/events'

directive('loading', ({ el, directive, component }) => {
    let targets = getTargets(el)

    let [delay, abortDelay] = applyDelay(directive)

    whenTargetsArePartOfRequest(component, targets, [
        () => delay(() => toggleBooleanStateDirective(el, directive, true)),
        () => abortDelay(() => toggleBooleanStateDirective(el, directive, false)),
    ])

    whenTargetsArePartOfFileUpload(component, targets, [
        () => delay(() => toggleBooleanStateDirective(el, directive, true)),
        () => abortDelay(() => toggleBooleanStateDirective(el, directive, false)),
    ])
})

function applyDelay(directive) {
    if (! directive.modifiers.includes('delay') || directive.modifiers.includes('none')) return [i => i(), i => i()]

    let duration = 200

    let delayModifiers = {
        'shortest': 50,
        'shorter': 100,
        'short': 150,
        'default': 200,
        'long': 300,
        'longer': 500,
        'longest': 1000,
    }

    Object.keys(delayModifiers).some(key => {
        if (directive.modifiers.includes(key)) {
            duration = delayModifiers[key]

            return true
        }
    })

    let timeout
    let started = false

    return [
        (callback) => { // Initiate delay...
            timeout = setTimeout(() => {
                callback()

                started = true
            }, duration)
        },
        async (callback) => { // Execute or abort...
            if (started) {
                await callback()
                started = false
            } else {
                clearTimeout(timeout)
            }
        },
    ]
}

function whenTargetsArePartOfRequest(component, targets, [ startLoading, endLoading ]) {
    on('commit', ({ component: iComponent, commit: payload, respond }) => {
        if (iComponent !== component) return

        if (targets.length > 0 && ! containsTargets(payload, targets)) return

        startLoading()

        respond(() => {
            endLoading()
        })
    })
}

function whenTargetsArePartOfFileUpload(component, targets, [ startLoading, endLoading ]) {
    let eventMismatch = e => {
        let { id, property } = e.detail

        if (id !== component.id) return true
        if (targets.length > 0 && ! targets.map(i => i.target).includes(property)) return true

        return false
    }

    window.addEventListener('livewire-upload-start', e => {
        if (eventMismatch(e)) return

        startLoading()
    })

    window.addEventListener('livewire-upload-finish', e => {
        if (eventMismatch(e)) return

        endLoading()
    })

    window.addEventListener('livewire-upload-error', e => {
        if (eventMismatch(e)) return

        endLoading()
    })
}

function containsTargets(payload, targets) {
    let { updates, calls } = payload

    return targets.some(({ target, params }) => {
        if (params) {
            return calls.some(({ method, params: methodParams }) => {
                return target === method
                    && params === quickHash(JSON.stringify(methodParams))
            })
        }

        let hasMatchingUpdate = Object.keys(updates).some(property => {
            return property.startsWith(target)
        })

        if (hasMatchingUpdate) return true

        if (calls.map(i => i.method).includes(target)) return true
    })
}

function getTargets(el) {
    let directives = getDirectives(el)

    let targets = []

    if (directives.has('target')) {
        let directive = directives.get('target')

        let raw = directive.expression

        if (raw.includes('(') && raw.includes(')')) {
            targets.push({ target: directive.method, params: quickHash(JSON.stringify(directive.params)) })
        } else if (raw.includes(',')) {
            raw.split(',').map(i => i.trim()).forEach(target => {
                targets.push({ target })
            })
        } else {
            targets.push({ target: raw })
        }
    } else {
        // If there is no wire:target, let's check for the existance of a wire:click="foo" or something,
        // and automatically scope this loading directive to that action.
        let nonActionOrModelLivewireDirectives = [ 'init', 'dirty', 'offline', 'target', 'loading', 'poll', 'ignore', 'key', 'id' ]

        directives
            .all()
            .filter(i => ! nonActionOrModelLivewireDirectives.includes(i.value))
            .map(i => i.expression.split('(')[0])
            .forEach(target => targets.push({ target }))
    }

    return targets
}

function quickHash(subject) {
    return btoa(encodeURIComponent(subject))
}
