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

    // `wire:dirty.persist` measures against the last *saved* state rather than the last
    // server response, so a round-trip that doesn't save (a poll, a lazy load, a live
    // model update, any unrelated action) no longer reports the component as clean...
    let persist = directive.modifiers.includes('persist')

    let oldIsDirty = false

    let initialDisplay = el.style.display

    let refreshDirtyState = (isDirty) => {
        toggleBooleanStateDirective(el, directive, isDirty, initialDisplay)

        oldIsDirty = isDirty
    }

    // Re-derive the state rather than assuming "clean" after a round-trip. For a plain
    // `wire:dirty` this is the same answer the old hard-coded `false` gave (canonical and
    // reactive match once the snapshot is merged), but a `.persist` directive measures
    // against the baseline and must stay dirty until it is explicitly rebaselined.
    // `force` re-applies even when the value is unchanged, because the morph has just
    // replaced the element's inline style with the server's markup...
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

    // "canonical" is the last server response and is replaced on every round-trip.
    // "baseline" is the last state the component considers saved. Touching the version
    // keeps the surrounding Alpine effect subscribed to rebaselines...
    if (persist) component.baselineVersion.value

    let reference = persist ? component.baseline : component.canonical

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
