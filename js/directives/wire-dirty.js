import { directive, getDirectives } from '@/directives'
import { toggleBooleanStateDirective } from './shared'
import { dataGet, WeakBag } from '@/utils'
import { on } from '@/hooks'

let refreshDirtyStatesByComponent = new WeakBag

on('commit', ({ component, succeed }) => {
    succeed(() => {
        setTimeout(() => { // Doing a "setTimeout" to let morphdom do its thing first...
            refreshDirtyStatesByComponent.each(component, i => i(false))
        })
    })
})

directive('dirty', ({ el, directive, component }) => {
    let targets = dirtyTargets(el)

    let oldIsDirty = false

    let initialDisplay = el.style.display

    let refreshDirtyState = (isDirty) => {
        toggleBooleanStateDirective(el, directive, isDirty, initialDisplay)

        oldIsDirty = isDirty
    }

    refreshDirtyStatesByComponent.add(component, refreshDirtyState)

    Alpine.effect(() => {
        let isDirty = false

        isDirty = checkDirty(component, targets.length === 0 ? undefined : targets)

        if (oldIsDirty !== isDirty) {
            refreshDirtyState(isDirty)
        }

        oldIsDirty = isDirty
    })
})

export function checkDirty(component, targets) {
    let isDirty = false

    if (targets === undefined) {
        isDirty = JSON.stringify(component.canonical) !== JSON.stringify(component.reactive)
    } else if (Array.isArray(targets)) {
        for (let i = 0; i < targets.length; i++) {
            let target = targets[i]

            let canonical = JSON.stringify(dataGet(component.canonical, target))
            let reactive = JSON.stringify(dataGet(component.reactive, target))

            if (canonical !== reactive) {
                isDirty = true
            }
        }
    } else {
        isDirty = JSON.stringify(dataGet(component.canonical, targets)) !== JSON.stringify(dataGet(component.reactive, targets))
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
