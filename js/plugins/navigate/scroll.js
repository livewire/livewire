
export function storeScrollInformationInHtmlBeforeNavigatingAway() {
    document.body.setAttribute('data-scroll-x', document.body.scrollLeft)
    document.body.setAttribute('data-scroll-y', document.body.scrollTop)

    document.querySelectorAll(['[x-navigate\\:scroll]', '[wire\\:scroll]']).forEach(el => {
        el.setAttribute('data-scroll-x', el.scrollLeft)
        el.setAttribute('data-scroll-y', el.scrollTop)
    })
}

export function restoreScrollPositionOrScrollToTop() {
    let scroll = el => {
        if (! el.hasAttribute('data-scroll-x')) {
            window.scrollTo({ top: 0, left: 0, behavior: 'instant' })
        } else {
            el.scrollTo({
                top: Number(el.getAttribute('data-scroll-y')),
                left: Number(el.getAttribute('data-scroll-x')),
                behavior: 'instant',
            })
            el.removeAttribute('data-scroll-x')
            el.removeAttribute('data-scroll-y')
        }
    }

    queueMicrotask(() => {
        queueMicrotask(() => { // Double microtask here to make sure scrolling restoration is the LAST thing to happen. (Even after Alpine's x-init functions)...
            scroll(document.body)

            document.querySelectorAll(['[x-navigate\\:scroll]', '[wire\\:scroll]']).forEach(scroll)
        })
    })
}
