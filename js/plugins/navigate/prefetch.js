import { performFetch } from "@/plugins/navigate/fetch";
import { getUriStringFromUrlObject } from "./links";
import { storeCurrentPageStatus } from "./history";
import { interceptRequest } from "@/request";

// Warning: this could cause some memory leaks
let prefetches = {}

// Default prefetch cache duration is 30 seconds...
let cacheDuration = 30000

interceptRequest(({ onSend }) => {
    onSend(() => clearPrefetches())
})

export function prefetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination)

    if (prefetches[uri]) return

    let state = { finished: false, html: null, whenFinished: () => {}, whenFailed: () => {}, expiry: null }

    prefetches[uri] = state

    // Bound the lifetime of an in-flight prefetch. If it takes too long,
    // invalidate it and allow any waiting navigation to fall back.
    state.expiry = setTimeout(() => {
        invalidatePrefetch(uri, state)
    }, cacheDuration)

    performFetch(uri, (html, routedUri, status) => {
        if (prefetches[uri] !== state) return

        storeCurrentPageStatus(status)

        callback(html, routedUri)
    }, () => {
        if (prefetches[uri] !== state) return

        if (state.expiry) clearTimeout(state.expiry)

        delete prefetches[uri]

        errorCallback()

        // If navigation was waiting for this in-flight prefetch,
        // fall back to the normal navigation request.
        state.whenFailed()
    })
}

export function storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination) {
    let uri = getUriStringFromUrlObject(destination)
    let state = prefetches[uri]

    // If it takes too long, invalidate it and allow any waiting navigation to fall back.
    if (! state) return

    state.html = html
    state.finished = true
    state.finalDestination = finalDestination

    // The cache lifetime begins once the HTML is available.
    if (state.expiry) clearTimeout(state.expiry)

    state.expiry = setTimeout(() => {
        invalidatePrefetch(uri, state)
    }, cacheDuration)

    state.whenFinished()
}

export function getPretchedHtmlOr(destination, receive, ifNoPrefetchExists) {
    let uri = getUriStringFromUrlObject(destination)

    if (! prefetches[uri]) return ifNoPrefetchExists()

    if (prefetches[uri].finished) {
        let state = prefetches[uri]
        let html = state.html
        let finalDestination = state.finalDestination

        if (state.expiry) clearTimeout(state.expiry)

        delete prefetches[uri]

        return receive(html, finalDestination)
    }

    // Navigation is waiting for the in-flight prefetch.
    prefetches[uri].whenFinished = () => {
        // The prefetch may have been invalidated before completion.
        let state = prefetches[uri]

        if (! state) return ifNoPrefetchExists()

        let html = state.html
        let finalDestination = state.finalDestination

        if (state.expiry) clearTimeout(state.expiry)

        delete prefetches[uri]

        receive(html, finalDestination)
    }

    // Someone is waiting on this in-flight prefetch. If it fails, they're
    // left hanging with no navigation at all, so send them down the normal
    // request path instead where a failure can be handled properly...
    prefetches[uri].whenFailed = ifNoPrefetchExists
}

function clearPrefetches() {
    for (let uri in prefetches) {
        invalidatePrefetch(uri)
    }
}

function invalidatePrefetch(uri, state = prefetches[uri]) {
    if (! state) return

    // Don't let an old prefetch invalidate a newer one for the same URI.
    if (prefetches[uri] !== state) return

    if (state.expiry) clearTimeout(state.expiry)

    delete prefetches[uri]

    state.whenFailed()
}
