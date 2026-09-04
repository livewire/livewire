import { on as hook } from '@/hooks'
import { setNextActionOrigin } from '@/request'

hook('effect', ({ component, effects }) => {
    let listeners = []

    if (Object.prototype.hasOwnProperty.call(effects, 'listeners') && effects.listeners) {
        listeners = effects.listeners
    }

    registerListeners(component, listeners)
})

function registerListeners(component, listeners) {
    listeners.forEach(name => {
        // Register a global listener...
        let handler = (e) => {
            if (component.isLazy && ! component.hasBeenLazyLoaded) return

            if (e.__livewire) e.__livewire.receivedBy.push(component)

            // Event listeners may live in a completely different component than
            // the element that dispatched the event (for example, a global toast).
            // Preserve that element as the action origin so it receives data-loading
            // for the listener's request.
            if (e.target instanceof Element) {
                setNextActionOrigin({ el: e.target })
            }

            component.$wire.call('__dispatch', name, e.detail || {})
        }

        window.addEventListener(name, handler)

        component.addCleanup(() => window.removeEventListener(name, handler))

        // Register a listener for when "to" or "self"
        component.el.addEventListener(name, (e) => {
            if (component.isLazy && ! component.hasBeenLazyLoaded) return

            // We don't care about non-Livewire dispatches...
            if (! e.__livewire) return

            // We don't care about Livewire bubbling dispatches (only "to" and "self")...
            if (e.bubbles) return

            if (e.__livewire) e.__livewire.receivedBy.push(component.id)

            component.$wire.call('__dispatch', name, e.detail || {})
        })
    })
}

