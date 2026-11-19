import { globalDirective } from "@/directives"
import { createUrlObjectFromString } from '@/plugins/navigate/links'
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
        ignore: directive.modifiers.includes('ignore'),
    }

    if (options.ignore) return

    let href = el.getAttribute('href')

    if (href === null || href.startsWith('#')) return

    let classes = expression.split(' ').filter(String)

    let refreshCurrent = url => {
        let hrefUrl = createUrlObjectFromString(href)

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

export function pathMatches(hrefUrl, actualUrl, options = {}) {
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
