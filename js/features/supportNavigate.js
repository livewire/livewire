import { on, trigger } from "@/events"

let isNavigating = false

window.addEventListener('alpine:navigated', () => {
    isNavigating = true
})

on('redirect', ({ url, preventDefault, effects }) => {
    let forceNavigate = effects.redirectUsingNavigate

    if (forceNavigate || isNavigating) {
        preventDefault()

        Alpine.navigate(url)
    }
})

