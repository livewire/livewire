import { skipTransitionWhenDialogOpens } from '@/directives/wire-transition'

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

    skipTransitionWhenDialogOpens(
        document.startViewTransition(update)
    )
}
