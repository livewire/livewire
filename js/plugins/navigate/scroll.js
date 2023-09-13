import Alpine from 'alpinejs'

export function scrollToTop() {
    document.body.scrollTo(0, 0)
}

export function storeScrollInformationInHtmlBeforeNavigatingAway() {
    document.body.setAttribute('data-scroll-x', document.body.scrollLeft)
    document.body.setAttribute('data-scroll-y', document.body.scrollTop)

    document.querySelectorAll([`[${Alpine.prefixed('navigate\\:scroll')}]`, '[wire\\:scroll]']).forEach(el => {
        el.setAttribute('data-scroll-x', el.scrollLeft)
        el.setAttribute('data-scroll-y', el.scrollTop)
    })
}

export function restoreScrollPositionOrScrollToTop() {
    let scroll = el => {
        if (! el.hasAttribute('data-scroll-x')) {
            window.scrollTo(0, 0)
        } else {
            el.scrollTo(Number(el.getAttribute('data-scroll-x')), Number(el.getAttribute('data-scroll-y')))
            el.removeAttribute('data-scroll-x')
            el.removeAttribute('data-scroll-y')
        }
    }

    queueMicrotask(() => {
        scroll(document.body)

        document.querySelectorAll([`[${Alpine.prefixed('navigate\\:scroll')}]`, '[wire\\:scroll]']).forEach(scroll)
    })
}
