import store from '@/Store'
import Message from '@/Message';

export default function() {
    let initialized = false

    store.registerHook('component.initialized', component => {
        if (initialized) return

        let fauxResponse = {
            fingerprint: { ...component.fingerprint },
            serverMemo: { ...component.serverMemo },
            effects: { html: component.el.outerHTML }
        }

        replaceState(component, fauxResponse, component.effects['routePath'] ?? window.location.href)

        initialized = true
    })

    store.registerHook('message.received', ({ response }, component) => {
        if (response.effects['routePath'] === undefined) return

        pushState(component, response, response.effects['routePath'])
    })

    window.addEventListener('popstate', event => {
        if (event && event.state && event.state.livewire) {
            let { name, response } = event.state.livewire
            let component = store.getComponentsByName(name)[0]

            // We don't want to trigger a new pushState, so we'll remove the routePath
            response.effects['routePath'] = undefined

            // Now we'll replay that response immediately so that we render without delay
            let message = new Message(component, component.updateQueue) // FIXME: Discuss?
            message.storeResponse(response)
            component.handleResponse(message)

            // Finally, we'll refresh the component so that if anything has changed on
            // the server, we'll get those updates (similar to stale-while-revalidate)
            component.call('$refresh')
        }
    })
}

function replaceState(component, message, path) {
    history.replaceState(generateStateObject(component, message), '', path)
}

function pushState(component, message, path) {
    history.pushState(generateStateObject(component, message), '', path)
}

function generateStateObject(component, response) {
    let state = {
        turbolinks: {},
        livewire: {
            name: component.name,
            response
        }
    }

    console.warn(component.name, state.livewire.response.effects)

    return state
}
