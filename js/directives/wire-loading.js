import { toggleBooleanStateDirective } from './shared'
import { directive, getDirectives } from "@/directives"
import { closestIsland } from '@/features/supportIslands'
import { onNavigationStart } from '@/plugins/navigate/navigation'
import { interceptMessage } from '@/request'
import { listen } from '@/utils'

directive('loading', ({ el, directive, component, cleanup }) => {
    let { targets, inverted } = getTargets(el)

    let restoreLoadingState = () => toggleBooleanStateDirective(el, directive, false)
    let activeLoadingCount = 0

    let startLoading = () => {
        if (activeLoadingCount === 0) {
            if (directive.modifiers.includes('attr')) {
                let attribute = directive.expression
                let value = el.getAttribute(attribute)

                restoreLoadingState = value === null
                    ? () => el.removeAttribute(attribute)
                    : () => el.setAttribute(attribute, value)
            }

            toggleBooleanStateDirective(el, directive, true)
        }

        activeLoadingCount++
    }

    let endLoading = () => {
        if (activeLoadingCount === 0) return

        activeLoadingCount--

        if (activeLoadingCount === 0) restoreLoadingState()
    }

    let startLoadingWithDelay = applyDelay(directive, startLoading, endLoading)

    let cleanupA = whenTargetsArePartOfRequest(component, el, targets, inverted, startLoadingWithDelay)

    let cleanupB = whenTargetsArePartOfFileUpload(component, targets, startLoadingWithDelay)

    cleanup(() => {
        cleanupA()
        cleanupB()
    })
})

function applyDelay(directive, startLoading, endLoading) {
    if (! directive.modifiers.includes('delay') || directive.modifiers.includes('none')) {
        return () => {
            startLoading()

            return endLoading
        }
    }

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

    return () => {
        let started = false

        let timeout = setTimeout(() => {
            startLoading()

            started = true
        }, duration)

        return () => {
            if (started) {
                endLoading()
            } else {
                clearTimeout(timeout)
            }
        }
    }
}

function whenTargetsArePartOfRequest(component, el, targets, inverted, startLoading) {
    return interceptMessage(({ message, onSend, onSuccess, onFinish }) => {
        if (component !== message.component) return

        // When explicit targets are set via wire:target, skip island scope filtering
        // and let the target matching handle scoping. This allows wire:loading to
        // respond to any request containing the target, regardless of island boundaries...
        if (targets.length === 0) {
            let island = closestIsland(el)

            // If an island is found, see if the message has an action for the island and return if not...
            if (island && ! message.hasActionForIsland(island)) {
                return
            }

            // If no island is found, see if the message has an action for the component and return if not...
            if (! island && ! message.hasActionForComponent()) {
                return
            }
        }

        let matches = true
        let cleared = false
        let navigationWasStarted = false
        let stopWaitingForNavigation = () => {}
        let endLoading = () => {}

        let finishLoading = () => {
            if (! matches || cleared) return

            stopWaitingForNavigation()

            endLoading()
            cleared = true
        }

        onSend(({ payload }) => {
            if (targets.length > 0 && containsTargets(payload, targets) === inverted) {
                matches = false
            }

            if (! matches) return

            endLoading = startLoading()
        })

        // Clear loading before morph on success
        onSuccess(({ onEffect }) => {
            // Effects are processed before onEffect runs, so watch this window for a navigation...
            stopWaitingForNavigation = onNavigationStart(navigation => {
                navigationWasStarted = true

                navigation.onDestinationSettled(finishLoading)
            })

            onEffect(() => {
                stopWaitingForNavigation()

                if (navigationWasStarted) return

                finishLoading()
            })
        })

        // Clear loading on cancel/error/failure (onFinish fires immediately on these paths)
        onFinish(() => {
            stopWaitingForNavigation()

            if (navigationWasStarted) return

            finishLoading()
        })
    })
}

function whenTargetsArePartOfFileUpload(component, targets, startLoading) {
    let endLoadingByProperty = new Map

    let eventMismatch = e => {
        let { id, property } = e.detail

        if (id !== component.id) return true
        if (targets.length > 0 && ! targets.map(i => i.target).includes(property)) return true

        return false
    }

    let finishLoading = e => {
        if (eventMismatch(e)) return

        let { property } = e.detail
        let pending = endLoadingByProperty.get(property)
        let endLoading = pending?.shift()

        endLoading?.()

        if (pending?.length === 0) endLoadingByProperty.delete(property)
    }

    let cleanupA = listen(window, 'livewire-upload-start', e => {
        if (eventMismatch(e)) return

        let { property } = e.detail
        let pending = endLoadingByProperty.get(property) ?? []

        pending.push(startLoading())
        endLoadingByProperty.set(property, pending)
    })

    let cleanupB = listen(window, 'livewire-upload-finish', finishLoading)

    let cleanupC = listen(window, 'livewire-upload-error', finishLoading)

    return () => {
        cleanupA()
        cleanupB()
        cleanupC()
    }
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
            // If the property is nested, like `foo.bar`, we need to check if the root `foo` is the target.
            if (property.includes('.')) {
                let propertyRoot = property.split('.')[0]

                if (propertyRoot === target) return true
            }

            return property === target
        })

        if (hasMatchingUpdate) return true

        if (calls.map(i => i.method).includes(target)) return true
    })
}

function getTargets(el) {
    let directives = getDirectives(el)

    let targets = []

    let inverted = false

    if (directives.has('target')) {
        let directive = directives.get('target')

        if (directive.modifiers.includes("except")) inverted = true

        directive.methods.forEach(({ method, params }) => {
            targets.push({
                target: method,
                params: params && params.length > 0 ? quickHash(JSON.stringify(params)) : undefined
            })
        })
    } else {
        // If there is no wire:target, let's check for the existance of a wire:click="foo" or something,
        // and automatically scope this loading directive to that action.
        let nonActionOrModelLivewireDirectives = [ 'init', 'dirty', 'offline', 'navigate', 'target', 'loading', 'poll', 'ignore', 'key', 'id' ]

        directives
            .all()
            .filter(i => ! nonActionOrModelLivewireDirectives.includes(i.value))
            .map(i => i.expression.split('(')[0])
            .forEach(target => targets.push({ target }))
    }

    return { targets, inverted }
}

function quickHash(subject) {
    return btoa(encodeURIComponent(subject))
}
