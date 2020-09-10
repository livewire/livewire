import store from '@/Store'
import Message from '@/Message';

export default function () {

    let initializedPath = false

    // This is to prevent exponentially increasing the size of our state on page refresh.
    if (window.history.state) window.history.state.livewire = {};

    store.registerHook('component.initialized', component => {
        if (! component.effects.path) return

        let state = generateNewState(component, generateInitialFauxResponse(component))
        let url = initializedPath ? undefined : component.effects.path

        store.callHook('beforeReplaceState', state, url, component)

        history.replaceState(state, '', url)
        initializedPath = true
    })

    store.registerHook('message.processed', (message, component) => {
        if (message.replaying) return

        let { response } = message
        let effects = response.effects || {}

        if ('path' in effects && effects.path !== window.location.href) {
            let state = generateNewState(component, response)

            store.callHook('beforePushState', state, effects.path, component)

            history.pushState(state, '', effects.path)
        }
    })

    window.addEventListener('popstate', event => {
        if (!(event && event.state && event.state.livewire)) return

        Object.entries(event.state.livewire).forEach(([id, storageKey]) => {
            let component = store.findComponent(id)
            if (! component) return

            let response = JSON.parse(sessionStorage.getItem(storageKey))

            if (! response) return console.warn(`Livewire: sessionStorage key not found: ${storageKey}`)

            let message = new Message(component, [])
            message.storeResponse(response)
            message.replaying = true

            component.handleResponse(message)
        })
    })

    function generateNewState(component, response, cache = {}) {
        let state = (history.state && history.state.livewire) ? { ...history.state.livewire } : {}

        let storageKey = Math.random().toString(36).substring(2)

        sessionStorage.setItem(storageKey, JSON.stringify(response))

        state[component.id] = storageKey

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
