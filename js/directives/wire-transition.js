import { globalDirective } from "@/directives"

let defaultName = 'match-element'

globalDirective('transition', ({ el, directive, cleanup }) => {
    el.style.viewTransitionName = directive.expression || defaultName
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

    // After a morph, newly added wire:transition elements need their viewTransitionName
    // set synchronously. Alpine's MutationObserver would normally handle this, but its
    // internal queueMicrotask batching delays processing by one microtask hop — and the
    // View Transitions API's "activate" step captures the new DOM state in between,
    // before Alpine has a chance to initialize the directive...
    let ensureTransitionNames = () => {
        fromEl.querySelectorAll('[wire\\:transition]').forEach(el => {
            if (! el.style.viewTransitionName) {
                el.style.viewTransitionName = el.getAttribute('wire:transition') || defaultName
            }
        })
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

    let update = () => {
        callback()

        ensureTransitionNames()
    }

    let transitionConfig = { update }

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
        let transition = document.startViewTransition(update)

        transition.finished.finally(() => {
            style.remove()
        })

        await transition.updateCallbackDone
    }
}