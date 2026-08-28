import { directive, getDirectives } from '@/directives'
import { toggleBooleanStateDirective } from './shared'
import { dataGet, WeakBag } from '@/utils'
import { on } from '@/hooks'

let refreshDirtyStatesByComponent = new WeakBag

on('commit', ({ component, succeed }) => {
    succeed(() => {
        setTimeout(() => { // Doing a "setTimeout" to let morphdom do its thing first...
            refreshDirtyStatesByComponent.each(component, recompute => recompute(true))
        })
    })
})

directive('dirty', ({ el, directive, component }) => {
    let targets = dirtyTargets(el)
    let persist = directive.modifiers.includes('persist')

    let oldIsDirty = false

    let initialDisplay = el.style.display

    let refreshDirtyState = (isDirty) => {
        toggleBooleanStateDirective(el, directive, isDirty, initialDisplay)

        oldIsDirty = isDirty
    }

    let recompute = (force = false) => {
        let isDirty = checkDirty(component, targets.length === 0 ? undefined : targets, persist)

        if (force || oldIsDirty !== isDirty) {
            refreshDirtyState(isDirty)
        }

        oldIsDirty = isDirty
    }

    refreshDirtyStatesByComponent.add(component, recompute)

    Alpine.effect(() => recompute())
})

export function checkDirty(component, targets, persist = false) {
    let isDirty = false
    let reference = component.canonical

    if (persist) {
        component.baselineVersion.value

        reference = component.baseline
    }

    if (targets === undefined) {
        isDirty = JSON.stringify(reference) !== JSON.stringify(component.reactive)
    } else if (Array.isArray(targets)) {
        for (let i = 0; i < targets.length; i++) {
            if (isDirty) break;

            let target = targets[i]

            isDirty = JSON.stringify(dataGet(reference, target)) !== JSON.stringify(dataGet(component.reactive, target))
        }
    } else {
        isDirty = JSON.stringify(dataGet(reference, targets)) !== JSON.stringify(dataGet(component.reactive, targets))
    }

    return isDirty
}

function dirtyTargets(el) {
    let directives = getDirectives(el)
    let targets = []

    if (directives.has('model')) {
        targets.push(directives.get('model').expression)
    }

    if (directives.has('target')) {
        targets = targets.concat(
            directives
                .get('target')
                .expression.split(',')
                .map(s => s.trim())
        )
    }

    return targets
}
