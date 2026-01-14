import { globalDirective } from "@/directives"

globalDirective('transition', ({ el, directive, cleanup }) => {
    let transitionName = directive.expression || 'match-element'

    el.style.viewTransitionName = transitionName
})

export async function transitionDomMutation(fromEl, toEl, callback, options = {}) {
    // Skip transitions entirely if requested...
    if (options.skip) return callback()

    // Only transition if there is a [wire:transition] element within either the from or to elements...
    if (! fromEl.querySelector('[wire\\:transition]') && ! toEl.querySelector('[wire\\:transition]')) return callback()

    // Check if View Transitions API is supported...
    if (typeof document.startViewTransition !== 'function') {
        return callback()
    }

    // Disable root transitions for the page...
    let style = document.createElement('style')

    style.textContent = `
        @media (prefers-reduced-motion: reduce) {
            ::view-transition-group(*), ::view-transition-old(*), ::view-transition-new(*) {
                animation: none !important;
            }
        }

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

    let transitionConfig = {
        update: () => callback(),
    }

    // Add transition types if provided...
    if (options.type) {
        transitionConfig.types = [options.type]
    }

    try {
        let transition = document.startViewTransition(transitionConfig)

        transition.finished.finally(() => {
            style.remove()
        })

        await transition.updateCallbackDone
    } catch (e) {
        // Firefox 144+ supports View Transitions but only with a callback, not a config object (no transition types support)
        let transition = document.startViewTransition(() => callback())

        transition.finished.finally(() => {
            style.remove()
        })

        await transition.updateCallbackDone
    }
}