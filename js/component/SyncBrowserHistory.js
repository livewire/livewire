import store from '@/Store'
import Message from '@/Message';

export default function () {

    let initializedPath = false;

    store.registerHook('component.initialized', component => {
        let state = generateNewState(component, generateInitialFauxResponse(component))
        let url = initializedPath ? undefined : component.effects.path

        history.replaceState(state, '', url)
        initializedPath = true
    })

    store.registerHook('message.processed', (message, component) => {
        if (message.replaying) return

        let { response } = message
        let effects = response.effects || {}

        if ('path' in effects && effects.path !== window.location.href) {
            let state = generateNewState(component, response)

            history.pushState(state, '', effects.path)
        }
    })

    window.addEventListener('popstate', event => {
        if (!(event && event.state && event.state.livewire)) return

        Object.entries(event.state.livewire).forEach(([id, response]) => {
            let component = store.findComponent(id)
            if (! component) return

            let message = new Message(component, [])
            message.storeResponse(response)
            message.replaying = true

            component.handleResponse(message)
        })
    })

    function generateNewState(component, response, cache = {}) {
        let state = (history.state && history.state.livewire) ? { ...history.state.livewire } : {}

        state[component.id] = response

        return { livewire: state }
    }

    function generateInitialFauxResponse(component) {
        let { fingerprint, serverMemo, effects, el } = component

        return {
            fingerprint,
            serverMemo,
            effects: { ...effects, html: el.outerHTML }
        }
    }
}
