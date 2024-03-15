shouldHideProgressBar() && Alpine.navigate.disableProgressBar()

document.addEventListener('alpine:navigated', e => {
    // Forward a "livewire" version of the Alpine event...
    document.dispatchEvent(new CustomEvent('livewire:navigated', { bubbles: true }))
})

document.addEventListener('alpine:navigating', e => {
    // Forward a "livewire" version of the Alpine event...
    document.dispatchEvent(new CustomEvent('livewire:navigating', { bubbles: true }))
})

document.addEventListener('alpine:navigate', e => {
    // Forward a "livewire" version of the Alpine event...
    const cancelableEvent = new CustomEvent('livewire:navigate', {
        cancelable: true,
        bubbles: true,
        detail: {
            url: e.detail.url
        }
    })

    document.dispatchEvent(cancelableEvent) // Dispatch cancelable event

    if (cancelableEvent.defaultPrevented) {
        e.preventDefault()
    }
})

export function shouldRedirectUsingNavigateOr(effects, url, or) {
    let forceNavigate = effects.redirectUsingNavigate

    if (forceNavigate) {
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
