import store from '@/Store'
import queryString from '@/util/query-string'

export default function() {
    store.registerHook('component.initialized', component => {
        let state = {
            fingerprint: component.fingerprint,
            data: component.serverMemo.data,
            dataMeta: component.serverMemo.dataMeta,
        }

        // FIXME
        // replaceState(state, window.location.href)
    })

    window.addEventListener('popstate', event => {
        // console.warn(event)
        if (event && event.state && event.state.livewire) {
            // let component = store.getComponentsByName(event.state.livewire.component)[0];
            // store.callHook('responseReceived', component, event.state.livewire.response)

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

    store.registerHook('message.received', ({response}, component) => {
        if (response.effects['routePath'] === undefined) return

        // FIXME: component.effects vs. response.effects
        let routePath = response.effects['routePath']
        console.warn(response);
        history.replaceState({}, '', routePath);

        let state = generateStateObject(component, response)

        // if (component.useReplaceState === true) {
        //     component.useReplaceState = false
        //
        //     replaceState(component.name, response, routePath)
        // } else {
        //    pushState(component.name, response, routePath)
        // }
    })
}

function replaceState(state, path) {
    history.replaceState(state, '', path)
}

function pushState(state, path) {
    history.pushState(state, '', path)
}

function generateStateObject(component, response) {
    // This makes it so that Turbolinks doesn't break Livewire on the back button.
    let state = { turbolinks: {} }

    // Store the current Livewire state in the history stack, so that
    // when a user hits a back button, we can re-apply the state from this
    // point in time to the Livewire components.
    state.livewire = { component, response }

    return state
}
