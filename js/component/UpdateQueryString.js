import store from '@/Store'
import queryString from '@/util/query-string'

export default function () {
    store.registerHook('componentInitialized', (component) => {
        if (! component.meta.fromQueryString) return

        replaceState(component, component.meta.fromQueryString)
    })

    window.addEventListener('popstate', (event) => {
        if (event && event.state && event.state.livewire) {
            Object.keys(event.state.livewire.updates).forEach(name => {
                let component = store.getComponentsByName(name)[0]

                if (component) {
                    let updates = event.state.livewire.updates[name].data

                    Object.keys(updates).forEach(dataKey => {
                        component.set(dataKey, updates[dataKey])
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
        if (component.meta.fromQueryString === undefined) return

        if (component.useReplaceState === true) {
            component.useReplaceState = false

            replaceState(component, component.meta.fromQueryString)
        } else {
            pushState(component, component.meta.fromQueryString)
        }
    })
}

function replaceState(component, queryStringUpdateObject) {
    updateState('replace', component, queryStringUpdateObject)
}

function pushState(component, queryStringUpdateObject) {
    updateState('push', component, queryStringUpdateObject)
}

function updateState(type, component, queryStringUpdateObject) {
    var dataForQueryString = dataDestinedForQueryString(queryStringUpdateObject, component)

    var stringifiedQueryString = queryString.stringify(dataForQueryString)

    var state = generateStateObject(dataForQueryString, component)

    if (type === 'replace') {
        history.replaceState(state, "", [window.location.pathname, stringifiedQueryString].filter(Boolean).join('?'))
    } else {
        history.pushState(state, "", [window.location.pathname, stringifiedQueryString].filter(Boolean).join('?'))
    }
}

function dataDestinedForQueryString(queryStringUpdateObject, component) {
    var excepts = []
    var dataForQueryString = {}

    if (Array.isArray(queryStringUpdateObject)) {
        // User passed in a plain array to `$fromQueryString`
        queryStringUpdateObject.forEach(i => dataForQueryString[i] = component.data[i])
    } else {
        // User specified an "except", and therefore made this an object.
        Object.keys(queryStringUpdateObject).forEach(key => {
            if (isNaN(key)) {
                // If the key is non-numeric (presumably has an "except" key)
                dataForQueryString[key] = component.get(key)

                if (queryStringUpdateObject[key].except !== undefined) {
                    excepts.push({key: key, value: queryStringUpdateObject[key].except})
                }
            } else {
                // If key is numeric.
                const dataKey = queryStringUpdateObject[key]
                dataForQueryString[dataKey] = component.get(dataKey)
            }
        })
    }

    var queryData = window.location.search
        ? {...queryString.parse(window.location.search), ...dataForQueryString}
        : dataForQueryString

    // Remove data items that are specified in the "except" key option.
    excepts.forEach(({ key, value }) => {
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
