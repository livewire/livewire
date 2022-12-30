import { on } from './../synthetic/index'
import { findComponent } from "../state";

export default function () {
    on('effects', (target, effects) => {
        let dispatches = effects.dispatches
        if (! dispatches) return

        let component = findComponent(target.__livewireId)

        dispatches.forEach(({ event, data }) => {
            data = data || {}

            let e = new CustomEvent(event, {
                bubbles: true,
                detail: data,
            })

            component.el.dispatchEvent(e)
        })
    })
}
