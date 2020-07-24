import store from '@/Store'
import queryString from '@/util/query-string'

export default function () {
    store.registerHook('componentInitialized', (component) => {
        if (! component.effects['query']) return;

        let { properties, excepts } = component.effects['query']

        replaceState(component, properties, excepts)
    })

    window.addEventListener('popstate', (event) => {
        if (event && event.state && event.state.livewire) {
            Object.keys(event.state.livewire.updates).forEach(name => {
                let component = store.getComponentsByName(name)[0]

                if (! component.effects['query']) return;

                if (component) {
                    let updates = event.state.livewire.updates[name].data

                    component.effects['query'].properties.forEach(property => {
                        component.set(property, updates[property])
                    })

                    // This is so that when component.set() triggers a roundtrip,
                    // the response received from that roundtrip uses "replaceState"
                    // to update the query string so that it doesn't wipe out future state
                    // (disabling the "forward button") by using pushState.
                    component.useReplaceState = true
                }
            })
        }
    })

    store.registerHook('responseReceived', (component, response) => {
        if (component.effects['query'] === undefined) return

        let { properties, excepts } = component.effects['query']

        if (component.useReplaceState === true) {
            component.useReplaceState = false

            replaceState(component, properties, excepts)
        } else {
            pushState(component, properties, excepts)
        }
    })
}

function replaceState(component, properties, excepts) {
    updateState('replace', component, properties, excepts)
}

function pushState(component, properties, excepts) {
    updateState('push', component, properties, excepts)
}

function updateState(type, component, properties, excepts) {
    var dataForQueryString = dataDestinedForQueryString(component, properties, excepts)

    var stringifiedQueryString = queryString.stringify(dataForQueryString)

    var state = generateStateObject(dataForQueryString, component)

    if (type === 'replace') {
        history.replaceState(state, "", [window.location.pathname, stringifiedQueryString].filter(Boolean).join('?'))
    } else {
        history.pushState(state, "", [window.location.pathname, stringifiedQueryString].filter(Boolean).join('?'))
    }
}

function dataDestinedForQueryString(component, properties, excepts) {
    var dataForQueryString = {}

    properties.forEach(i => dataForQueryString[i] = component.get(i))

    var queryData = window.location.search
        ? {...queryString.parse(window.location.search), ...dataForQueryString}
        : dataForQueryString

    // Remove data items that are specified in the "except" key option.
    Object.entries(excepts).forEach(([key, value]) => {
        if (queryData[key] == value) {
            delete queryData[key]
        }
    })

    return queryData
}

function generateStateObject(dataDestinedForQueryString, component) {
    // This makes it so that Turbolinks doesn't break Livewire on the back button.
    let state = { turbolinks: {} }

    // Store the current Livewire state in the history stack, so that
    // when a user hits a back button, we can re-apply the state from this
    // point in time to the Livewire components.
    state.livewire = { updates: {} }
    state.livewire.updates[component.name] = {
        data: dataDestinedForQueryString,
    }

    return state
}
