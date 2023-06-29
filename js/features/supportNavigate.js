import { on, trigger } from "@/events"

let isNavigating = false

window.addEventListener('alpine:navigated', () => {
    isNavigating = true

    // Forward a "livewire" version of the Alpine event...
    window.dispatchEvent(new CustomEvent('livewire:navigated', { bubbles: true }))
})

on('redirect', ({ url, preventDefault, effects }) => {
    let forceNavigate = effects.redirectUsingNavigate

    if (forceNavigate || isNavigating) {
        preventDefault()

        Alpine.navigate(url)
    }
})

