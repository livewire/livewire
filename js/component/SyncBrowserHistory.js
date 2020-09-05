import store from '@/Store'
import Message from '@/Message';

export default function () {

    let cachedComponentMemos = {}
    let initializedPath = false;

    store.registerHook('component.initialized', component => {
        let state = generateNewState(component, generateFauxResponse(component))
        let url = initializedPath ? undefined : component.effects.path

        history.replaceState(state, '', url)
        initializedPath = true
    })

    store.registerHook('message.receiving', (message, component) => {
        cachedComponentMemos[component.id] = JSON.parse(JSON.stringify(component.serverMemo));
    });

    store.registerHook('message.processed', (message, component) => {
        let { replaying, response } = message
        if (replaying) return

        let { effects } = response

        if ('path' in effects && effects.path !== window.location.href) {

            let lastComponentServerMemo = component.id in cachedComponentMemos
                ? cachedComponentMemos[component.id]
                : {}

            let state = generateNewState(component, response, lastComponentServerMemo)
            cachedComponentMemos[component.id] = {}

            history.pushState(state, '', effects.path)
        }
    })

    window.addEventListener('popstate', event => {
        if (!(event && event.state && event.state.livewire)) return

        Object.entries(event.state.livewire).forEach(([id, response]) => {
            let component = store.findComponent(id)
            if (!component) return

            let message = new Message(component, [])
            message.storeResponse(response)
            message.replaying = true

            component.handleResponse(message)
            component.call('$refresh')
        })
    })

    function generateNewState(component, response, cache = {}) {
        let state = (history.state && history.state.livewire) ? { ...history.state.livewire } : {}

        let data = {
            ...response,
            serverMemo: {
                ...cache,
                ...response.serverMemo,
                data: {
                    ...cache.data,
                    ...response.serverMemo.data,
                }
            }
        }

        state[component.id] = JSON.parse(JSON.stringify(data))

        return { turbolinks: {}, livewire: state }
    }

    function generateFauxResponse(component) {
        let { fingerprint, serverMemo, effects, el } = component
        return {
            fingerprint,
            serverMemo,
            effects: { ...effects, html: el.outerHTML }
        }
    }
}
