import { on } from './../synthetic/index'
import { directives } from "../directives"
import { findComponent } from 'state'

export default function () {
    on('element.init', (el, component) => {
        let elDirectives = directives(el)

        if (elDirectives.missing('loading')) return

        let targets = getTargets(elDirectives)

        let loadingDirectives = elDirectives.directives.filter(i => i.type === 'loading')

        loadingDirectives.forEach(directive => {
            let [delay, abortDelay] = applyDelay(directive)

            let inverted = boolean => directive.modifiers.includes('remove') ? ! boolean : boolean

            setLoading(el, directive, inverted(false))

            whenTargetsArePartOfRequest(component, targets, [
                () => delay(() => setLoading(el, directive, inverted(true))),
                () => abortDelay(() => setLoading(el, directive, inverted(false))),
            ])
        })
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

    function setLoading(el, directive, isLoading) {
        if (directive.modifiers.includes('class')) {
            let classes = directive.value.split(' ')

            if (isLoading) {
                el.classList.add(...classes)
            } else {
                el.classList.remove(...classes)
            }
        } else if (directive.modifiers.includes('attr')) {
            if (isLoading) {
                el.setAttribute(directive.value, true)
            } else {
                el.removeAttribute(directive.value)
            }
        } else {
            let display = (['inline', 'block', 'table', 'flex', 'grid', 'inline-flex']
                .filter(i => directive.modifiers.includes(i))[0] || 'inline-block')

            el.style.display = isLoading ? display : 'none'
        }
    }

    function getTargets(elDirectives) {
        let targets = []

        if (elDirectives.has('target')) {
            let directive = elDirectives.get('target')

            let raw = directive.value

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

            elDirectives
                .all()
                .filter(i => ! nonActionOrModelLivewireDirectives.includes(i.type))
                .map(i => i.value.split('(')[0])
                .forEach(target => targets.push({ target }))
        }

        return targets
    }

    function quickHash(subject) {
        return btoa(encodeURIComponent(subject))
    }
}
