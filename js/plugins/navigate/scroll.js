
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
            scroll(document.body)

            document.querySelectorAll(['[x-navigate\\:scroll]', '[wire\\:navigate\\:scroll]']).forEach(scroll)
        })
    })
}

export function restoreScrollPositionOrScrollToHash(destination, preserveScroll) {
    if (destination.hash) {
        queueMicrotask(() => {
            queueMicrotask(() => {
                let fragment = destination.hash.slice(1)
                let element = document.getElementById(fragment)

                if (! element) {
                    try {
                        let decodedFragment = decodeURIComponent(fragment)

                        element = document.getElementById(decodedFragment)

                        // Match the browser's fragment navigation behavior, including legacy named anchors.
                        if (! element) {
                            element = [...document.getElementsByTagName('a')]
                                .find(el => el.getAttribute('name') === decodedFragment)
                        }

                        if (! element && decodedFragment.toLowerCase() === 'top') {
                            window.scrollTo({ top: 0, left: 0, behavior: 'instant' })

                            return
                        }
                    } catch {
                        // Ignore malformed percent-encoding.
                    }
                }

                element?.scrollIntoView()
            })
        })

        return
    }

    ! preserveScroll && restoreScrollPositionOrScrollToTop()
}
