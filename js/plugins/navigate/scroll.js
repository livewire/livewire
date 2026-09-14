
export function storeScrollInformationInHtmlBeforeNavigatingAway() {
    document.body.setAttribute('data-scroll-x', document.body.scrollLeft)
    document.body.setAttribute('data-scroll-y', document.body.scrollTop)

    document.querySelectorAll(['[x-navigate\\:scroll]', '[wire\\:navigate\\:scroll]']).forEach(el => {
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
            let shouldScrollToFragment = ! document.body.hasAttribute('data-scroll-x')

            scroll(document.body)

            document.querySelectorAll(['[x-navigate\\:scroll]', '[wire\\:navigate\\:scroll]']).forEach(scroll)

            if (shouldScrollToFragment) {
                getFragmentTarget()?.scrollIntoView({ behavior: 'instant' })
            }
        })
    })
}

function getFragmentTarget() {
    let fragment = window.location.hash.substring(1)

    if (! fragment) return

    let target = document.getElementById(fragment)

    if (target) return target

    try {
        fragment = decodeURIComponent(fragment)
    } catch (e) {
        // A malformed escape sequence can still be a valid element ID or name.
    }

    return document.getElementById(fragment)
        || Array.from(document.getElementsByName(fragment)).find(el => el.tagName === 'A')
}
