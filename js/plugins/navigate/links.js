
export function whenThisLinkIsPressed(el, callback) {
    let isProgrammaticClick = e => ! e.isTrusted
    let isNotPlainLeftClick = e => (e.which > 1) || (e.altKey) || (e.ctrlKey) || (e.metaKey) || (e.shiftKey)
    let isNotPlainEnterKey = (e) => (e.which !== 13) || (e.altKey) || (e.ctrlKey) || (e.metaKey) || (e.shiftKey)

    el.addEventListener('click', e => {
        // This allows programmatic clicking of links via: `$0.click()`...
        if (isProgrammaticClick(e)) {
            e.preventDefault()

            callback(whenReleased => whenReleased())

            return
        }

        // This allows cmd+click and such to still work as expected...
        if (isNotPlainLeftClick(e)) return;

        // If it's a plain left click, we want to prevent "click" and let "mouseup" do its thing...
        e.preventDefault()
    })

    el.addEventListener('mousedown', e => {
        // We only care about left clicks for wire:navigate...
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
        // We only care about the enter key...
        if (isNotPlainEnterKey(e)) return;

        e.preventDefault()

        callback(whenReleased => whenReleased())
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
    return urlString !== null && new URL(urlString, document.baseURI)
}

export function getUriStringFromUrlObject(urlObject) {
    return urlObject.pathname + urlObject.search + urlObject.hash
}
