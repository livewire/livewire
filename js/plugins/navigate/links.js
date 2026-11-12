
export function whenThisLinkIsPressed(el, callback) {
    let isProgrammaticClick = e => ! e.isTrusted
    let isNotPlainLeftClick = e => (e.which > 1) || (e.altKey) || (e.ctrlKey) || (e.metaKey) || (e.shiftKey)
    let isNotPlainEnterKey = (e) => (e.which !== 13) || (e.altKey) || (e.ctrlKey) || (e.metaKey) || (e.shiftKey)

    el.addEventListener('click', e => {
        // This allows programmatic clicking of links via: `$0.click()`...
        if (isProgrammaticClick(e)) {
            if (linkShouldBeHandledNatively(el)) return;

            e.preventDefault()

            callback(whenReleased => whenReleased())

            return
        }

        // This allows cmd+click and such to still work as expected...
        if (isNotPlainLeftClick(e)) return;

        if (linkShouldBeHandledNatively(el)) return;

        // If it's a plain left click, we want to prevent "click" and let "mouseup" do its thing...
        e.preventDefault()
    })

    el.addEventListener('mousedown', e => {
        // We only care about left clicks for wire:navigate...
        if (isNotPlainLeftClick(e)) return;

        if (linkShouldBeHandledNatively(el)) return;

        // We don't cancel "mousedown" here. Cancelling it would keep focus in the previously
        // focused field (so its blur/change never fires), suppress :active styles, and
        // stop native drags from starting (like dragging a wire:sort item that's a link)...

        let pressedAt = { x: e.clientX, y: e.clientY }

        callback((whenReleased) => {
            let moved = false

            let cleanup = () => {
                document.removeEventListener('mouseup', onMouseUp, true)
                document.removeEventListener('mousemove', onMouseMove, true)
                document.removeEventListener('dragstart', onDragStart, true)
            }

            // Listen on the document so a press that's released somewhere else still cleans up after itself...
            let onMouseUp = e => {
                cleanup()

                // Only a press that's released on this link, without dragging, is a click...
                if (! el.contains(e.target) || moved) return

                e.preventDefault()

                requestAnimationFrame(() => {
                    whenReleased()
                })
            }

            // A drag that doesn't use native drag events (like a SortableJS "forceFallback" drag) only shows up as pointer movement...
            let onMouseMove = e => {
                if (Math.hypot(e.clientX - pressedAt.x, e.clientY - pressedAt.y) > dragThreshold) moved = true
            }

            // If the press turns into a native drag (of this link or of a draggable ancestor), it isn't a click, so don't navigate...
            let onDragStart = e => {
                cleanup()

                // Links are draggable by default. Cancelling "mousedown" used to stop that default drag, so keep stopping it,
                // otherwise it takes over drags that don't use native drag events. Links that opt in with draggable="true"
                // (like SortableJS's native drag items) still drag...
                if (isDefaultDragOf(el, e)) e.preventDefault()
            }

            document.addEventListener('mouseup', onMouseUp, true)
            document.addEventListener('mousemove', onMouseMove, true)
            document.addEventListener('dragstart', onDragStart, true)
        })
    })

    el.addEventListener("keydown", e => {
        // We only care about the enter key...
        if (isNotPlainEnterKey(e)) return;

        if (linkShouldBeHandledNatively(el)) return;

        e.preventDefault()

        callback(whenReleased => whenReleased())
    })
}

// How far (in px) the pointer can move during a press before it counts as a drag instead of a click...
let dragThreshold = 5

function isDefaultDragOf(el, e) {
    // Dragging selected text reports the text node as the target...
    let source = e.target instanceof Element ? e.target : e.target.parentElement

    if (! source || ! el.contains(source)) return false

    let optedIn = source.closest('[draggable="true"]')

    return ! (optedIn && el.contains(optedIn))
}

export function whenThisLinkIsHoveredOrFocusedFor(el, ms = 60, callback) {
    for (let [enterEvent, leaveEvent] of [['mouseenter', 'mouseleave'], ['focus', 'blur']]) {
        el.addEventListener(enterEvent, e => {
            let timeout = setTimeout(() => {
                callback(e)
            }, ms)

            let handler = () => {
                clearTimeout(timeout)
                el.removeEventListener(leaveEvent, handler)
            }

            el.addEventListener(leaveEvent, handler)
        })
    }
}

export function extractDestinationFromLink(linkEl) {
    return createUrlObjectFromString(linkEl.getAttribute('href'))
}

export function createUrlObjectFromString(urlString) {
    return urlString !== null && new URL(urlString, document.baseURI)
}

export function isSameOrigin(destination) {
    return !! destination && destination.origin === window.location.origin
}

export function isSamePageFragment(destination) {
    return isSameOrigin(destination)
        && destination.pathname === window.location.pathname
        && destination.search === window.location.search
        && destination.href.includes('#')
}

export function visitNatively(destination) {
    window.location.href = destination.href
}

export function linkShouldBeHandledNatively(linkEl, destination = extractDestinationFromLink(linkEl)) {
    if (! destination) return true
    if (! ['http:', 'https:'].includes(destination.protocol)) return true
    if (! isSameOrigin(destination)) return true
    if (linkEl.hasAttribute('download')) return true

    let target = linkEl.getAttribute('target')?.trim().toLowerCase()

    if (target && target !== '_self') return true

    return false
}

export function getUriStringFromUrlObject(urlObject) {
    return urlObject.pathname + urlObject.search + urlObject.hash
}
