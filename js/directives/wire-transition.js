import { globalDirective } from "@/directives"

let defaultName = 'match-element'

// No-op — viewTransitionName is now set dynamically by transitionDomMutation()
// to avoid creating permanent stacking contexts...
globalDirective('transition', ({ el, directive, cleanup }) => {
    //
})

function setTransitionNames(root) {
    root.querySelectorAll('[wire\\:transition]').forEach(el => {
        if (! el.style.viewTransitionName) {
            el.style.viewTransitionName = el.getAttribute('wire:transition') || defaultName
        }
    })
}

function clearTransitionNames(root) {
    root.querySelectorAll('[wire\\:transition]').forEach(el => {
        el.style.viewTransitionName = ''
    })
}

export async function transitionDomMutation(fromEl, toEl, callback, options = {}) {
    // Skip transitions entirely if requested...
    if (options.skip) return callback()

    // Only transition if there is a [wire:transition] element within either the from or to elements...
    if (! fromEl.querySelector('[wire\\:transition]') && ! toEl.querySelector('[wire\\:transition]')) return callback()

    // Check if View Transitions API is supported...
    if (typeof document.startViewTransition !== 'function') {
        return callback()
    }

    // Skip entirely if a modal dialog is already open (transitions behind
    // a dialog are invisible to the user and the ::view-transition pseudo-
    // elements would paint above the dialog during animation)...
    if (document.querySelector('dialog:modal')) return callback()

    // Set transition names right before the transition starts (not permanently)...
    setTransitionNames(fromEl)

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

        // After a morph, newly added wire:transition elements need their viewTransitionName
        // set synchronously. Alpine's MutationObserver would normally handle this, but its
        // internal queueMicrotask batching delays processing by one microtask hop — and the
        // View Transitions API's "activate" step captures the new DOM state in between,
        // before Alpine has a chance to initialize the directive...
        setTransitionNames(fromEl)
    }

    let transitionConfig = { update }

    // Add transition types if provided...
    if (options.type) {
        transitionConfig.types = [options.type]
    }

    let cleanup = () => {
        style.remove()
        clearTransitionNames(fromEl)
    }

    // Watch for modal dialogs opening during the transition (e.g., via Alpine x-effect).
    // ::view-transition pseudo-elements paint above the top layer, so elements with
    // wire:transition would visually appear above dialog modals during animation.
    // This observer catches showModal() the instant it sets the `open` attribute
    // and skips the transition before the browser paints a frame...
    let skipOnDialog = (transition) => {
        let observer = new MutationObserver(() => {
            if (document.querySelector('dialog:modal')) {
                transition.skipTransition()
                observer.disconnect()
            }
        })

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['open'],
            subtree: true,
        })

        transition.finished.finally(() => observer.disconnect())
    }

    try {
        let transition = document.startViewTransition(transitionConfig)

        skipOnDialog(transition)

        transition.finished.finally(cleanup)

        await transition.updateCallbackDone
    } catch (e) {
        // Firefox 144+ supports View Transitions but only with a callback, not a config object (no transition types support)
        let transition = document.startViewTransition(update)

        skipOnDialog(transition)

        transition.finished.finally(cleanup)

        await transition.updateCallbackDone
    }
}
