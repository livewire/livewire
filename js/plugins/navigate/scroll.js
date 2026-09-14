
export function storeScrollInformationInHtmlBeforeNavigatingAway() {
    document.body.setAttribute('data-scroll-x', document.body.scrollLeft)
    document.body.setAttribute('data-scroll-y', document.body.scrollTop)

    document.querySelectorAll(['[x-navigate\\:scroll]', '[wire\\:navigate\\:scroll]']).forEach(el => {
        // Match saved scroll positions to live containers during fragment history visits.
        if (! el.hasAttribute('data-scroll-id')) el.setAttribute('data-scroll-id', Math.random())

        el.setAttribute('data-scroll-x', el.scrollLeft)
        el.setAttribute('data-scroll-y', el.scrollTop)
    })
}

export function restoreScrollPositionOrScrollToTop({ scrollToFragment = false, snapshotHtml = null } = {}) {
    let savedElements = snapshotHtml
        ? new DOMParser().parseFromString(snapshotHtml, 'text/html').querySelectorAll('[data-scroll-id]')
        : []

    let savedScroll = new Map(Array.from(savedElements, el => [el.getAttribute('data-scroll-id'), el]))

    let scroll = el => {
        let source = savedScroll.get(el.getAttribute('data-scroll-id')) || el

        if (! source.hasAttribute('data-scroll-x')) {
            window.scrollTo({ top: 0, left: 0, behavior: 'instant' })
        } else {
            el.scrollTo({
                top: Number(source.getAttribute('data-scroll-y')),
                left: Number(source.getAttribute('data-scroll-x')),
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

            if (scrollToFragment && ! window.location.hash) {
                window.scrollTo({ top: 0, left: 0, behavior: 'instant' })
            } else if (shouldScrollToFragment || scrollToFragment) {
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
