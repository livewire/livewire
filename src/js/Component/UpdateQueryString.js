import store from '@/Store'
import queryString from 'query-string'

export default function () {
    store.registerHook('responseReceived', (component, response) => {
        if (response.updatesQueryString.length) {
            var dateDestinedForQueryString = {}

            response.updatesQueryString.forEach(i => dateDestinedForQueryString[i] = component.data[i])

            const stringifiedQueryString = queryString.stringify({
                ...queryString.parse(window.location.search),
                ...dateDestinedForQueryString,
            })

            history.replaceState(null, "", window.location.pathname+'?'+stringifiedQueryString)
        }
    })
}
