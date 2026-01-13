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
    let options = {
        exact: true,
    }

    // Find all links with any wire:navigate variation
    document.querySelectorAll(wireNavigateSelector).forEach(el => {
        // If the element has `wire:current` then the user might want to specify strict or 
        // exact matching, so we will just return early so we don't override that...
        if (Array.from(el.attributes).some(attr => attr.name.startsWith('wire:current'))) return

        // Fragment hrefs aren't supported...
        let href = el.getAttribute('href')

        if (! href || href.startsWith('#')) return

        try {
            let hrefUrl = new URL(href, window.location.href)

            // Check if this link matches the current URL (using default partial matching)
            if (pathMatches(hrefUrl, currentUrl, options)) {
                el.setAttribute('data-current', '')
            } else {
                el.removeAttribute('data-current')
            }
        } catch (e) {
            // Invalid URL, skip
        }
    })
}
