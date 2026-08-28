let useViewTransitions = false

export function enableViewTransitions() {
    useViewTransitions = true
}

export function transitionPageSwap(update) {
    if (! useViewTransitions) return update()

    // Check if the View Transitions API is supported...
    if (typeof document.startViewTransition !== 'function') return update()

    // Respect users who prefer reduced motion...
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return update()

    // Skip transitions behind an open top-layer element because the browser's
    // transition snapshots would paint above it...
    if (document.querySelector('dialog:modal, :popover-open')) return update()

    document.startViewTransition(update)
}
