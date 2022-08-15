import { directives } from "../directives"
import { on } from "../events"

export default function () {
    let loadingState = Alpine.reactive({ componentIds: [] })
    let componentTargets = {}

    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('loading')) return

        let loadingDirectives = allDirectives.directives.filter(i => i.type === 'loading')

        loadingDirectives.forEach(directive => {
            let duration = 0

            if (directive.modifiers.includes('delay')) {
                duration = 200

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
            }

            console.log('duration: ', duration)

            let delay = (value) => {
                if (duration === 0) return value

                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        reject(value)
                    }, duration)
                })
            }

            let isLoading = (el, component) => {
                let loading = loadingState.componentIds.includes(component.id)

                if (! loading) return delay(inverted(false))

                if (isTargeting(el)) {
                    return delay(inverted(hasTargets(el, componentTargets[component.id])))
                }

                return delay(inverted(true))
            }

            let inverted = boolean => directive.modifiers.includes('remove') ? ! boolean : boolean

            if (directive.modifiers.includes('class')) {
                let classes = directive.value.split(' ').filter(Boolean)

                Alpine.bind(el, {
                    async 'x-bind:class'() { return await isLoading(el, component) ? classes : [] }
                })
            } else if (directive.modifiers.includes('attr')) {
                let attr = directive.value

                Alpine.bind(el, {
                    async ['x-bind:'+attr]() { return await isLoading(el, component) }
                })
            } else {
                Alpine.bind(el, {
                    async ['x-effect']() {
                        let display = (['inline', 'block', 'table', 'flex', 'grid', 'inline-flex']
                            .filter(i => directive.modifiers.includes(i))[0]) || 'inline-block'

                        let shouldShow = await isLoading(el, component)

                        el.style.setProperty('display', shouldShow ? display : 'none', 'important')
                    }
                })
            }
        })
    })

    on('component.request', (component, payload) => {
        componentTargets[component.id] = getTargetingNamesFromPayload(payload)
        loadingState.componentIds.push(component.id)
    })

    on('component.response', component => {
        delete componentTargets[component.id]
        loadingState.componentIds = loadingState.componentIds.filter(i => i !== component.id)
    })
}

function getTargetingNamesFromPayload(payload) {
    let actions = payload.updates
        .filter(action => {
            return action.type === 'callMethod'
        })
        .map(action => action.payload.method)

    let actionsWithParams = payload.updates
        .filter(action => {
            return action.type === 'callMethod'
        })
        .map(action =>
            generateSignatureFromMethodAndParams(
                action.payload.method,
                action.payload.params
            )
        )

    let models = payload.updates
        .filter(action => {
            return action.type === 'syncInput'
        })
        .map(action => {
            let name = action.payload.name
            if (! name.includes('.')) {
                return name
            }

            let modelActions = []

            modelActions.push(
                name.split('.').reduce((fullAction, part) => {
                    modelActions.push(fullAction)

                    return fullAction + '.' + part
                })
            )

            return modelActions
        })
        .flat()

        return actions.concat(actionsWithParams).concat(models)
}

function generateSignatureFromMethodAndParams(method, params) {
    return method + btoa(encodeURIComponent(params.toString()))
}

let nonActionOrModelLivewireDirectives = ['init', 'dirty', 'offline', 'target', 'loading', 'poll', 'ignore', 'key', 'id']

function isTargeting(el) {
    let elDirectives = directives(el)

    if (elDirectives.has('target')) return true

    return directives(el).all().filter(i => !nonActionOrModelLivewireDirectives.includes(i.type)).length > 0
}

function hasTargets(el, targets) {
    let targeting = []

    let elDirectives = directives(el)

    if (elDirectives.get('target')) {
        let target = elDirectives.get('target')
        if (target.params.length > 0) {
            targeting = [
                generateSignatureFromMethodAndParams(
                    target.method,
                    target.params
                ),
            ]
        } else {
            // wire:target overrides any automatic loading scoping we do.
            targeting = target.value.split(',').map(s => s.trim())
        }
    } else {
        // If there is no wire:target, let's check for the existance of a wire:click="foo" or something,
        // and automatically scope this loading directive to that action.
        targeting = elDirectives
            .all()
            .filter(i => !nonActionOrModelLivewireDirectives.includes(i.type))
            .map(i => i.method)
    }

    return targets.some(i => targeting.includes(i))
}
