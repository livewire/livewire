import store from '@/Store'
import queryString from '@/util/query-string'

export default function () {
    store.registerHook('responseReceived', (component, response) => {
        if (response.historyPath === undefined) return

        history.replaceState({turbolinks: {}, livewire: component.data}, "", response.historyPath)
    })
}
