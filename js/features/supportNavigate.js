
shouldHideProgressBar() && Alpine.navigate.disableProgressBar()

document.addEventListener('alpine:navigate', e => forwardEvent('livewire:navigate', e))
document.addEventListener('alpine:navigating', e => forwardEvent('livewire:navigating', e))
document.addEventListener('alpine:navigated', e => forwardEvent('livewire:navigated', e))

function forwardEvent(name, original) {
    let event = new CustomEvent(name, { cancelable: true, bubbles: true, detail: original.detail })

    document.dispatchEvent(event)

    if (event.defaultPrevented) {
        original.preventDefault()
    }
}

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
