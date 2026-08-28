import { globalDirective } from "@/directives"

let defaultName = 'match-element'
let assignedTransitionNames = new WeakSet()

// No-op — viewTransitionName is now set dynamically by transitionDomMutation()
// to avoid creating permanent stacking contexts...
globalDirective('transition', ({ el, directive, cleanup }) => {
    //
})

export function setTransitionNames(root, options = {}) {
    let attribute = options.attribute ?? 'wire:transition'

    root.querySelectorAll(selectorForAttribute(attribute)).forEach(el => {
        if (el.style.viewTransitionName) return

        let name = el.getAttribute(attribute)

        // When the developer orchestrates a typed swap (e.g. #[Transition('forward')]),
        // unnamed wire:transition elements stay unnamed so they ride with the parent's
        // snapshot instead of becoming independent groups with the browser's default fade...
        if (! name && options.type) return

        el.style.viewTransitionName = name || defaultName
        assignedTransitionNames.add(el)
    })
}

export function clearTransitionNames(root, options = {}) {
    let attribute = options.attribute ?? 'wire:transition'

    root.querySelectorAll(selectorForAttribute(attribute)).forEach(el => {
        if (! assignedTransitionNames.has(el)) return

        el.style.viewTransitionName = ''
        assignedTransitionNames.delete(el)
    })
}

function selectorForAttribute(attribute) {
    return `[${attribute.replace(/[:.]/g, '\\$&')}]`
}

export function startViewTransition(update, options = {}) {
    let transitionConfig = { update }

    if (options.type) transitionConfig.types = [options.type]

    try {
        return document.startViewTransition(transitionConfig)
    } catch (e) {
        // Firefox supports the callback form but not typed transitions...
        return document.startViewTransition(update)
    }
}

export function skipTransitionWhenTopLayerOpens(transition) {
    // Chromium rejects `ready` when skipTransition() is called. Consume that
    // expected rejection so a deliberate policy skip is not reported as an error...
    transition.ready.catch(() => {})

    let onBeforeToggle = (event) => {
        if (event.newState === 'open' && event.target.matches?.('dialog, [popover]')) {
            transition.skipTransition()
        }
    }

    document.addEventListener('beforetoggle', onBeforeToggle, true)

    // Keep the attribute observer as a fallback for browsers that do not emit
    // beforetoggle when a modal dialog is opened...
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

    transition.finished.finally(() => {
        observer.disconnect()
        document.removeEventListener('beforetoggle', onBeforeToggle, true)
    }).catch(() => {})
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

    // Skip entirely if a top-layer element is already open (transitions behind
    // it are invisible to the user and the ::view-transition pseudo-elements
    // would paint above it during animation)...
    if (document.querySelector('dialog:modal, :popover-open')) return callback()

    // Set transition names right before the transition starts (not permanently)...
    setTransitionNames(fromEl, options)

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
        setTransitionNames(fromEl, options)
    }

    let cleanup = () => {
        style.remove()
        clearTransitionNames(fromEl)
    }

    let transition = startViewTransition(update, { type: options.type })

    skipTransitionWhenTopLayerOpens(transition)

    transition.finished.finally(cleanup).catch(() => {})

    await transition.updateCallbackDone
}
