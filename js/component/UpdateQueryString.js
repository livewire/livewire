import store from '@/Store'
import queryString from '@/util/query-string'

export default function () {
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


        history.replaceState({turbolinks: {}}, "", [window.location.pathname, stringifiedQueryString].filter(Boolean).join('?') + window.location.hash)
    })
}
