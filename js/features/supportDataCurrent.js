import { pathMatches } from '@/directives/wire-current'
import { wireNavigateSelector } from '@/directives/wire-navigate'

document.addEventListener('livewire:navigated', () => {
    updateNavigateLinks()
})

// Also run on initial page load
document.addEventListener('DOMContentLoaded', () => {
    updateNavigateLinks()
})

function updateNavigateLinks() {
    let currentUrl = new URL(window.location.href)

    // Find all links with any wire:navigate variation
    document.querySelectorAll(wireNavigateSelector).forEach(el => {
        // Fragment hrefs aren't supported...
        let href = el.getAttribute('href')

        if (! href || href.startsWith('#')) return

        try {
            let hrefUrl = new URL(href, window.location.href)

            // Check if this link matches the current URL (using default partial matching)
            if (pathMatches(hrefUrl, currentUrl)) {
                el.setAttribute('data-current', '')
            } else {
                el.removeAttribute('data-current')
            }
        } catch (e) {
            // Invalid URL, skip
        }
    })
}
