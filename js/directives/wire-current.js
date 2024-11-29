import { globalDirective } from "@/directives"
import Alpine from 'alpinejs'

Alpine.addInitSelector(() => `[wire\\:current]`)

let onPageChanges = new Map

document.addEventListener('livewire:navigated', () => {
    onPageChanges.forEach(i => i(new URL(window.location.href)))
})

globalDirective('current', ({ el, directive, cleanup }) => {
    let expression = directive.expression

    // Fragment hrefs aren't supported as of yet...
    if (expression.startsWith('#')) return

    let href = el.getAttribute('href')

    let classes = expression.split(' ').filter(String)

    let refreshCurrent = url => {
        if (href === url.pathname) {
            el.classList.add(...classes)
        } else {
            el.classList.remove(...classes)
        }
    }

    refreshCurrent(new URL(window.location.href))

    onPageChanges.set(el, refreshCurrent)

    cleanup(() => onPageChanges.delete(el))
})
