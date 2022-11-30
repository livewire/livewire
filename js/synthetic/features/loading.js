import { reactive } from "../index"
import { on } from "../events"

export default function () {
    on('new', target => {
        target.__loading = reactive({ state: false })
    })

    on('target.request', (target, payload) => {
        target.__loading.state = true

        return () => target.__loading.state = false
    })

    on('decorate', (target, path, addProp, decorator, symbol) => {
        addProp('$loading', { get() {
            return target.__loading.state
        }})
    })
}
