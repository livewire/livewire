import store from '@/Store'
import qs from '@/util/query-string'
import Message from '@/Message';

export default function() {

    store.registerHook('component.initialized', component => {
        let state = generateNewState(component, generateFauxResponse(component))
        let url = generateNewUrl(component.effects)

        history.replaceState(state, '', url)
        console.log(`Initialized ${component.name}`)
    })

    store.registerHook('message.received', (message, component) => {
        console.log('Received message:', message)
        let { replaying, response } = message
        if (replaying) return

        let { effects } = response

        let newResponse = JSON.parse(JSON.stringify(response))

        let state = generateNewState(component, newResponse)
        let url = generateNewUrl(effects)

        if (url) history.pushState(state, '', url)
    })

    window.addEventListener('popstate', event => {
        if (! (event && event.state && event.state.livewire)) return

        Object.entries(event.state.livewire).forEach(([id, response]) => {
            let component = store.findComponent(id)
            if (!component) return

            let message = new Message(component, component.updateQueue) // FIXME: Discuss?
            message.storeResponse(JSON.parse(JSON.stringify(response)))
            message.replaying = true

            console.log('About to replay:', message)

            component.handleResponse(message)
            setTimeout(() => component.call('$refresh'))
        })
    })

    function generateNewState(component, response) {
        let state = (history.state && history.state.livewire) ? { ...history.state.livewire } : {}

        state[component.id] = response

        return { turbolinks: {}, livewire: state }
    }

    function generateFauxResponse(component) {
        let { fingerprint, serverMemo, effects, el } = component
        let response = JSON.parse(JSON.stringify({
            fingerprint,
            serverMemo,
            effects: { ...effects, html: el.outerHTML }
        }));
        return response;
    }

    // FIXME: Move to server
    function generateNewUrl({ path, query }) {
        if (path === undefined && query === undefined) return

        let currentPath = window.location.pathname
        let currentQueryString = window.location.search.substr(1)
        let currentQuery = qs.parse(currentQueryString)

        if (path === undefined) path = currentPath
        if (query === undefined) query = {}

        let nextQueryString = qs.stringify({ ...currentQuery, ...query })

        if (currentPath === path && currentQueryString === nextQueryString) return

        return `${path}?${nextQueryString}`
    }
}
