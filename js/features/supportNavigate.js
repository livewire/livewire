import { on, trigger } from "@/events"

let isUsingAlpineNavigation = false

shouldHideProgressBar() && Alpine.navigate.disableProgressBar()

document.addEventListener('alpine:navigated', e => {
    if (!e.detail || !e.detail.isFistPageLoad) {
        isUsingAlpineNavigation = true
    }

    // Forward a "livewire" version of the Alpine event...
    document.dispatchEvent(new CustomEvent('livewire:navigated', { bubbles: true }))
})

document.addEventListener('alpine:navigating', e => {
    // Forward a "livewire" version of the Alpine event...
    document.dispatchEvent(new CustomEvent('livewire:navigating', { bubbles: true }))
})

export function shouldRedirectUsingNavigateOr(effects, url, or) {
    let forceNavigate = effects.redirectUsingNavigate

    if (forceNavigate || isUsingAlpineNavigation) {
        Alpine.navigate(url)
    } else {
        or()
    }
}

function shouldHideProgressBar() {
    if (!! document.querySelector('[data-no-progress-bar]')) return true

    if (window.livewireScriptConfig && window.livewireScriptConfig.progressBar === false) return true

    return false
}

