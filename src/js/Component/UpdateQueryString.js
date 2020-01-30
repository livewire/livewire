import store from '@/Store'
import queryString from '@/util/query-string'

export default function () {
    store.registerHook('responseReceived', (component, response) => {
        if (response.updatesQueryString.length) {
            var dataDestinedForQueryString = {}

            response.updatesQueryString.forEach(i => dataDestinedForQueryString[i] = component.data[i])

            const stringifiedQueryString = queryString.stringify({
                ...queryString.parse(window.location.search),
                ...dataDestinedForQueryString,
            })

            history.replaceState(null, "", window.location.pathname + '?' + stringifiedQueryString)
        }
    })
}
