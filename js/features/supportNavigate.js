import { on, trigger } from "@/events"

let isNavigating = false

document.addEventListener('alpine:navigated', e => {
    if (e.detail && e.detail.init) return

    isNavigating = true

    // Forward a "livewire" version of the Alpine event...
    document.dispatchEvent(new CustomEvent('livewire:navigated', { bubbles: true }))
})

export function shouldRedirectUsingNavigateOr(effects, url, or) {
    let forceNavigate = effects.redirectUsingNavigate

    if (forceNavigate || isNavigating) {
        Alpine.navigate(url)
    } else {
        or()
    }
}
