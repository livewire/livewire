import { on } from "@/events"

on('effects', (component, effects) => {
    if (! effects['redirect']) return

    let url = effects['redirect']

    window.location.href = url
})
