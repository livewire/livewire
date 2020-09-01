import store from '@/Store'
import Message from '../Message';

export default function() {
    store.registerHook('component.initialized', component => {
        let message = {
            fingerprint: { ...component.fingerprint },
            serverMemo: { ...component.serverMemo },
            effects: { html: component.el.outerHTML }
        }

        replaceState(component, message, component.effects['routePath'] ?? window.location.href)
    })

    store.registerHook('message.received', ({ response }, component) => {
        if (response.effects['routePath'] === undefined) return

        let routePath = response.effects['routePath']

        pushState(component, response, routePath)

        // if (component.useReplaceState === true) {
        //     component.useReplaceState = false
        //
        //     replaceState(component, response, routePath)
        // } else {
        //     pushState(component, response, routePath)
        // }
    })

    window.addEventListener('popstate', event => {
        if (event && event.state && event.state.livewire) {
            let component = store.getComponentsByName(event.state.livewire.component)[0];

            let message = new Message(component, component.updateQueue)

            let response = event.state.livewire.message;
            response.effects['routePath'] = undefined

            message.storeResponse(response)

            component.handleResponse(message);
            component.call('$refresh')

            // Object.keys(event.state.livewire.updates).forEach(name => {
            //     let component = store.getComponentsByName(name)[0]
            //
            //     if (!component.effects['query']) return
            //
            //     let { properties, excepts } = component.effects['query']
            //
            //     if (component) {
            //         let updates = event.state.livewire.updates[name].data
            //
            //         properties.forEach(property => {
            //             if (updates[property] === undefined) return
            //
            //             component.set(property, updates[property])
            //         })
            //
            //         // This is so that when component.set() triggers a roundtrip,
            //         // the response received from that roundtrip uses "replaceState"
            //         // to update the query string so that it doesn't wipe out future state
            //         // (disabling the "forward button") by using pushState.
            //         component.useReplaceState = true
            //     }
            // })
        }
    })
}

function replaceState(component, message, path) {
    history.replaceState(generateStateObject(component, message), '', path)
}

function pushState(component, message, path) {
    history.pushState(generateStateObject(component, message), '', path)
}

function generateStateObject(component, message) {
    let state = {
        turbolinks: {},
        livewire: {
            component: component.name,
            message
        }
    }

    console.log(state);

    return state;
}
