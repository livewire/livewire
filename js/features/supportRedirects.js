import { on, trigger } from "@/events"

on('effects', (component, effects) => {
    if (! effects['redirect']) return

    let url = effects['redirect']

    let prevented = false
    let preventDefault = () => prevented = true

    trigger('redirect', { component, url, preventDefault, effects })

    if (! prevented) window.location.href = url
})
