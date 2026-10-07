import { clearTransitionNames, setTransitionNames, skipTransitionWhenTopLayerOpens, startViewTransition } from '@/directives/wire-transition'

let type = 'navigate'
let attribute = 'wire:transition.navigate'
let navigateTransitionSelector = '[wire\\:transition\\.navigate]'
let defaultName = 'livewire-navigate'

export function transitionPageSwap(html, update) {
    let newDocument = new DOMParser().parseFromString(html, 'text/html')
    let currentDocumentTransitions = findNavigateTransitions(document)
    let newDocumentTransitions = findNavigateTransitions(newDocument)

    if (! currentDocumentTransitions.length && ! newDocumentTransitions.length) return update()

    // Check if the View Transitions API is supported...
    if (typeof document.startViewTransition !== 'function') return update()

    // Respect users who prefer reduced motion...
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return update()

    // Skip transitions behind an open top-layer element because the browser's
    // transition snapshots would paint above it...
    if (document.querySelector('dialog:modal, :popover-open')) return update()

    let useRootTransition = document.documentElement.matches(navigateTransitionSelector)
        || newDocument.documentElement.matches(navigateTransitionSelector)

    // Element-scoped transitions leave the document root stationary. Marking
    // <html> opts into the browser's full-page root transition instead...
    let style = useRootTransition ? null : disableRootTransition()

    setNavigateTransitionNames(document.body)

    let updateAndNameNewPage = () => {
        update()

        setNavigateTransitionNames(document.body)
    }

    let viewTransition = startViewTransition(updateAndNameNewPage, { type })

    skipTransitionWhenTopLayerOpens(viewTransition)

    viewTransition.finished
        .finally(() => {
            style?.remove()
            clearTransitionNames(document.body, { attribute, includeRoot: true })
        })
        .catch(() => {})
}

function findNavigateTransitions(subject) {
    let root = subject.documentElement
    let elements = Array.from(root.querySelectorAll(navigateTransitionSelector))

    if (root.matches(navigateTransitionSelector)) elements.unshift(root)

    return elements
}

function setNavigateTransitionNames(root) {
    setTransitionNames(root, {
        type,
        attribute,
        defaultName,
        includeRoot: true,
    })
}

function disableRootTransition() {
    let style = document.createElement('style')

    style.setAttribute('data-livewire-navigate-transition', '')

    style.textContent = `
        ::view-transition-old(root) {
            animation: none !important;
            opacity: 0 !important;
        }

        ::view-transition-new(root) {
            animation: none !important;
            opacity: 1 !important;
        }
    `

    document.head.appendChild(style)

    return style
}
