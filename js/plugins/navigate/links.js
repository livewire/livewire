
export function whenThisLinkIsClicked(el, callback) {
    el.addEventListener('click', e => {
        e.preventDefault()

        callback(el)
    })
}

export function whenThisLinkIsPressed(el, callback) {
    let isNotPlainLeftClick = e => (e.which > 1) || (e.altKey) || (e.ctrlKey) || (e.metaKey) || (e.shiftKey)

    let isNotPlainEnterKey = (e) => (e.which !== 13) || (e.altKey) || (e.ctrlKey) || (e.metaKey) || (e.shiftKey)

    el.addEventListener('click', e => {
        if (isNotPlainLeftClick(e)) return;

        e.preventDefault()
    })

    el.addEventListener('mousedown', e => {
        if (isNotPlainLeftClick(e)) return;

        e.preventDefault()

        callback((whenReleased) => {
            let handler = e => {
                e.preventDefault()

                whenReleased()

                el.removeEventListener('mouseup', handler)
            }

            el.addEventListener('mouseup', handler)
        })
    })

    el.addEventListener("keydown", e => {
        if (isNotPlainEnterKey(e)) return;

        e.preventDefault()

        callback((whenReleased) => {
            whenReleased()
        })
    })
}

export function whenThisLinkIsHoveredFor(el, ms = 60, callback) {
    el.addEventListener('mouseenter', e => {
        let timeout = setTimeout(() => {
            callback(e)
        }, ms)

        let handler = () => {
            clearTimeout(timeout)
            el.removeEventListener('mouseleave', handler)
        }

        el.addEventListener('mouseleave', handler)
    })
}

export function extractDestinationFromLink(linkEl) {
    return createUrlObjectFromString(linkEl.getAttribute('href'))
}

export function createUrlObjectFromString(urlString) {
    return new URL(urlString, document.baseURI)
}
