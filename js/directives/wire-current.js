import { globalDirective } from "@/directives"
import Alpine from 'alpinejs'

Alpine.addInitSelector(() => `[wire\\:current]`)

let onPageChanges = new Map

document.addEventListener('livewire:navigated', () => {
    onPageChanges.forEach(i => i(new URL(window.location.href)))
})

globalDirective('current', ({ el, directive, cleanup }) => {
    let expression = directive.expression

    let options = {
        exact: directive.modifiers.includes('exact'),
        strict: directive.modifiers.includes('strict'),
    }

    // Fragment hrefs aren't supported as of yet...
    if (expression.startsWith('#')) return

    // If there is no href, let's not do anything...
    if (! el.hasAttribute('href')) return

    let href = el.getAttribute('href')

    let hrefUrl = new URL(href, window.location.href)

    let classes = expression.split(' ').filter(String)

    let refreshCurrent = url => {
        if (pathMatches(hrefUrl, url, options)) {
            el.classList.add(...classes)
            el.setAttribute('data-current', '')
        } else {
            el.classList.remove(...classes)
            el.removeAttribute('data-current')
        }
    }

    refreshCurrent(new URL(window.location.href))

    onPageChanges.set(el, refreshCurrent)

    cleanup(() => onPageChanges.delete(el))
})

function pathMatches(hrefUrl, actualUrl, options) {
    // If the domains/hostnames don't match, we are not going to match...
    if (hrefUrl.hostname !== actualUrl.hostname) return false

    // Remove trailing slashes for consistency (if not .strict)...
    let hrefPath = options.strict ? hrefUrl.pathname : hrefUrl.pathname.replace(/\/+$/, '')
    let actualPath = options.strict ? actualUrl.pathname : actualUrl.pathname.replace(/\/+$/, '')

    if (options.exact) {
        return hrefPath === actualPath
    }

    let hrefPathSegments = hrefPath.split('/')
    let actualPathSegments = actualPath.split('/')


    for (let i = 0; i < hrefPathSegments.length; i++) {
        if (hrefPathSegments[i] !== actualPathSegments[i]) return false
    }

    return true
}
