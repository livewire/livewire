import store from '@/Store'
import queryString from 'query-string'

export default function () {
    store.registerHook('responseReceived', (component, response) => {
        if (response.updatesQueryString.length) {
            var data = {}
            response.updatesQueryString.forEach(i => data[i] = component.data[i])
            const lap = queryString.stringify({...queryString.parse(window.location.search), ...data})
            history.replaceState(null, "", window.location.pathname+'?'+lap)
        }
    })
}
