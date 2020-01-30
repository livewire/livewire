import store from '@/Store'
import url from '@/util/url'

export default function () {

    store.registerHook('responseReceived', (component, response) => {
        if (response.updatesQueryString.length) {
            var dataDestinedForQueryString = {}

            response.updatesQueryString.forEach(i => dataDestinedForQueryString[i] = component.data[i])

            var stringifiedQueryString = url.stringify({
                ...url.parse(window.location.search),
                ...dataDestinedForQueryString,
            });

            history.replaceState(null, "", window.location.pathname + '?' + stringifiedQueryString)
        }
    })
}
