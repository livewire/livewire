import { on } from '@/events'

on('effects', (component, effects) => {
    let dispatches = effects.dispatches
    if (! dispatches) return

    dispatches.forEach(({ event, data }) => {
        data = data || {}

        let e = new CustomEvent(event, {
            bubbles: true,
            detail: data,
        })

        component.el.dispatchEvent(e)
    })
})
