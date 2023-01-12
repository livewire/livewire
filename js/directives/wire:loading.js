import { toggleBooleanStateDirective } from './shared'
import { directive, getDirectives } from "@/directives"
import { findComponent } from '@/state'
import { on } from '@synthetic/index'

directive('loading', (el, directive, { component }) => {
    let targets = getTargets(el)

    let [delay, abortDelay] = applyDelay(directive)

    toggleBooleanStateDirective(el, directive, false)

    whenTargetsArePartOfRequest(component, targets, [
        () => delay(() => toggleBooleanStateDirective(el, directive, true)),
        () => abortDelay(() => toggleBooleanStateDirective(el, directive, false)),
    ])
})

function applyDelay(directive) {
    if (! directive.modifiers.includes('delay')) return [i => i(), i => i()]

    let duration = 200

    let delayModifiers = {
        'shortest': 50,
        'shorter': 100,
        'short': 150,
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
        (callback) => { // Execute or abort...
            if (started) {
                callback()
            } else {
                clearTimeout(timeout)
            }
        },
    ]
}

function whenTargetsArePartOfRequest(component, targets, [ startLoading, endLoading ]) {
    on('target.request', (target, payload) => {
        if (findComponent(target.__livewireId) !== component) return

        if (targets.length > 0 && ! containsTargets(payload, targets)) return

        startLoading()

        return () => {
            endLoading()
        }
    })
}

function containsTargets(payload, targets) {
    let { diff, calls } = payload

    return targets.some(({ target, params }) => {
        if (params) {
            return calls.some(({ method, params: methodParams}) => {
                return target === method
                    && params === quickHash(methodParams.toString())
            })
        }

        if (Object.keys(diff).map(i => i.split('.')[0]).includes(target)) return true

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
            targets.push({ target: directive.method, params: quickHash(directive.params.toString()) })
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
