import { setTransitionNames, clearTransitionNames, skipTransitionWhenDialogOpens } from '@/directives/wire-transition'

let type = 'navigate'

let useViewTransitions = false

export function enableViewTransitions() {
    useViewTransitions = true
}

export function transitionPageSwap(transition, update) {
    // Transition if enabled globally or requested for this navigation...
    if (! transition && ! useViewTransitions) return update()

    // Check if the View Transitions API is supported...
    if (typeof document.startViewTransition !== 'function') return update()

    // Respect users who prefer reduced motion...
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return update()

    // Skip entirely if a modal dialog is open (transitions behind a dialog
    // are invisible to the user and the ::view-transition pseudo-elements
    // would paint above the dialog during animation)...
    if (document.querySelector('dialog:modal')) return update()

    // Name [wire:transition="..."] elements on the outgoing page right before
    // the browser snapshots it. Only explicitly named elements participate —
    // matching names across pages morph, and unnamed elements stay part of
    // the page snapshot (a shared default name would collide across pages)...
    setTransitionNames(document.body, { type })

    let updateAndNameNewPage = () => {
        update()

        // The incoming page's elements need their names set synchronously,
        // before the browser captures the new snapshot...
        setTransitionNames(document.body, { type })
    }

    let viewTransition

    try {
        viewTransition = document.startViewTransition({ update: updateAndNameNewPage, types: [type] })
    } catch (e) {
        // Firefox 144+ supports View Transitions but only with a callback, not a config object (no transition types support)
        viewTransition = document.startViewTransition(updateAndNameNewPage)
    }

    skipTransitionWhenDialogOpens(viewTransition)

    // Clear the names after the animation so they don't create permanent
    // stacking contexts on the new page...
    viewTransition.finished.finally(() => clearTransitionNames(document.body))
}
