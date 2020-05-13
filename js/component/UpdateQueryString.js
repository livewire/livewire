import store from '@/Store'
import queryString from '@/util/query-string'

export default function () {
    window.addEventListener('onpopstate', (event) => {
        if (event && event.state && event.state.livewire) {
            Object.keys(event.state.livewire.updates).forEach(id => {
                let component = window.livewire.find(id)

                if (component) {
                    let updates = event.state.livewire.updates[id]

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
        if (response.updatesQueryString === undefined) return

        var excepts = []
        var dataDestinedForQueryString = {}

        if (Array.isArray(response.updatesQueryString)) {
            // User passed in a plain array to `$updatesQueryString`
            response.updatesQueryString.forEach(i => dataDestinedForQueryString[i] = component.data[i])
        } else {
            // User specified an "except", and therefore made this an object.
            Object.keys(response.updatesQueryString).forEach(key => {
                if (isNaN(key)) {
                    // If the key is non-numeric (presumably has an "except" key)
                    dataDestinedForQueryString[key] = component.data[key]

                    if (response.updatesQueryString[key].except !== undefined) {
                        excepts.push({key: key, value: response.updatesQueryString[key].except})
                    }
                } else {
                    // If key is numeric.
                    const dataKey = response.updatesQueryString[key]
                    dataDestinedForQueryString[dataKey] = component.data[dataKey]
                }
            })
        }

        var queryData = {
            ...queryString.parse(window.location.search),
            ...dataDestinedForQueryString,
        }

        // Remove data items that are specified in the "except" key option.
        excepts.forEach(({ key, value }) => {
            if (queryData[key] == value) {
                delete queryData[key]
            }
        })

        var stringifiedQueryString = queryString.stringify(queryData)

        // This makes it so that Turbolinks doesn't break Livewire on the back button.
        let state = { turbolinks: {} }

        // Store the current Livewire state in the history stack, so that
        // when a user hits a back button, we can re-apply the state from this
        // point in time to the Livewire components.
        state.livewire = { updates: {} }
        state.livewire.updates[component.id] = dataDestinedForQueryString

        if (component.useReplaceState === true) {
            component.useReplaceState = false
            history.replaceState(state, "", [window.location.pathname, stringifiedQueryString].filter(Boolean).join('?'))
        } else {
            history.pushState(state, "", [window.location.pathname, stringifiedQueryString].filter(Boolean).join('?'))
        }
    })
}
