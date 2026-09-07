import { performFetch } from "@/plugins/navigate/fetch";
import { getUriStringFromUrlObject } from "./links";
import { storeCurrentPageStatus } from "./history";
import { interceptRequest } from "@/request";

// Warning: this could cause some memory leaks
let prefetches = {}

// Default prefetch cache duration is 30 seconds...
let cacheDuration = 30000

// A Livewire request can change the application state,
// so discard all prefetched HTML before the request is sent.
// If a navigation is already waiting on an in-flight prefetch, fail it so it
// falls back to a fresh navigation request.
interceptRequest(({ onSend }) => {
    onSend(() => {
        Object.values(prefetches).forEach(state => {
            if (! state.finished) state.whenFailed()
        })

        prefetches = {}
    })
})

export function prefetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination)

    if (prefetches[uri]) return

    prefetches[uri] = { finished: false, html: null, whenFinished: () => setTimeout(() => delete prefetches[uri], cacheDuration), whenFailed: () => {} }

    performFetch(uri, (html, routedUri, status) => {
        let state = prefetches[uri]

        // The prefetch may have been invalidated while its request was in flight.
        if (! state) return

        storeCurrentPageStatus(status)

        // Don't cache redirected responses. The prefetch is keyed by the original
        // URL, so caching the redirected response could cause a later navigation
        // to use stale or unauthorized HTML.
        if (getUriStringFromUrlObject(routedUri) !== uri) {
            let whenFailed = state.whenFailed

            delete prefetches[uri]

            whenFailed()

            return
        }

        callback(html, routedUri)
    }, () => {
        let state = prefetches[uri]

        // The prefetch may have been invalidated while its request was in flight.
        if (! state) return

        let whenFailed = state.whenFailed

        // If the fetch failed, remove the prefetch so it gets attempted again...
        delete prefetches[uri]

        errorCallback()

        whenFailed()
    })
}

export function storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination) {
    let state = prefetches[getUriStringFromUrlObject(destination)]

    // The prefetch may have been invalidated while its request was in flight.
    if (! state) return

    state.html = html
    state.finished = true
    state.finalDestination = finalDestination
    state.whenFinished()
}

export function getPretchedHtmlOr(destination, receive, ifNoPrefetchExists) {
    let uri = getUriStringFromUrlObject(destination)

    if (! prefetches[uri]) return ifNoPrefetchExists()

    if (prefetches[uri].finished) {
        let html = prefetches[uri].html
        let finalDestination = prefetches[uri].finalDestination

        delete prefetches[uri]

        return receive(html, finalDestination)
    } else {
        prefetches[uri].whenFinished = () => {
            let html = prefetches[uri].html
            let finalDestination = prefetches[uri].finalDestination

            delete prefetches[uri]

            receive(html, finalDestination)
        }

        // Someone is waiting on this in-flight prefetch. If it fails, they're
        // left hanging with no navigation at all, so send them down the normal
        // request path instead where a failure can be handled properly...
        prefetches[uri].whenFailed = ifNoPrefetchExists
    }
}

