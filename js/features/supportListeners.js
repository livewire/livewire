import { on as hook } from '@/hooks'

hook('effect', ({ component, effects }) => {
    registerListeners(component, effects.listeners || [])
})

function registerListeners(component, listeners) {
    listeners.forEach(name => {
        // Register a global listener...
        let handler = (e) => {
            if (e.__livewire) e.__livewire.receivedBy.push(component)

            component.$wire.call('__dispatch', name, e.detail || {})
        }

        window.addEventListener(name, handler)

        component.addCleanup(() => window.removeEventListener(name, handler))

        // Register a listener for when "to" or "self"
        component.el.addEventListener(name, (e) => {
            // We don't care about non-Livewire dispatches...
            if (! e.__livewire) return

            // We don't care about Livewire bubbling dispatches (only "to" and "self")...
            if (e.bubbles) return

            if (e.__livewire) e.__livewire.receivedBy.push(component.id)

            component.$wire.call('__dispatch', name, e.detail || {})
        })
    })
}


