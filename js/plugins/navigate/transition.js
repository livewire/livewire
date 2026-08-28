import { clearTransitionNames, setTransitionNames } from '@/directives/wire-transition'

let type = 'navigate'
let attribute = 'wire:transition.navigate'
let useViewTransitions = false
let navigateTransitionSelector = '[wire\\:transition\\.navigate]'

export function enableViewTransitions() {
    useViewTransitions = true
}

export function transitionPageSwap(html, update) {
    if (! shouldTransition(html)) return update()

    // Check if the View Transitions API is supported...
    if (typeof document.startViewTransition !== 'function') return update()

    // Respect users who prefer reduced motion...
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return update()

    // Skip transitions behind an open top-layer element because the browser's
    // transition snapshots would paint above it...
    if (document.querySelector('dialog:modal, :popover-open')) return update()

    // Assign names just for the transition so they don't permanently create
    // stacking contexts on either page...
    setTransitionNames(document.body, { type, attribute })

    let updateAndNameNewPage = () => {
        update()

        setTransitionNames(document.body, { type, attribute })
    }

    let viewTransition

    try {
        viewTransition = document.startViewTransition({ update: updateAndNameNewPage, types: [type] })
    } catch (e) {
        // Firefox supports the callback form but not typed transitions...
        viewTransition = document.startViewTransition(updateAndNameNewPage)
    }

    viewTransition.finished
        .finally(() => clearTransitionNames(document.body, { attribute }))
        .catch(() => {})
}

function shouldTransition(html) {
    if (useViewTransitions) return true

    if (document.body.querySelector(navigateTransitionSelector)) return true

    let newDocument = new DOMParser().parseFromString(html, 'text/html')

    return !! newDocument.body.querySelector(navigateTransitionSelector)
}
