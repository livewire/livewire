import { performFetch } from "@/plugins/navigate/fetch";
import { getUriStringFromUrlObject } from "./links";

// Warning: this could cause some memory leaks
let prefetches = {}

// Default prefetch cache duration is 30 seconds...
let cacheDuration = 30000

export function prefetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination)

    if (prefetches[uri]) return

    prefetches[uri] = { finished: false, html: null, whenFinished: () => setTimeout(() => delete prefetches[uri], cacheDuration), whenFailed: () => {} }

    performFetch(uri, (html, routedUri, status) => {
        callback(html, routedUri, status)
    }, () => {
        let whenFailed = prefetches[uri].whenFailed

        // If the fetch failed, remove the prefetch so it gets attempted again...
        delete prefetches[uri]

        errorCallback()

        whenFailed()
    })
}

export function storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination, status) {
    let state = prefetches[getUriStringFromUrlObject(destination)]
    state.html = html
    state.finished = true
    state.finalDestination = finalDestination
    state.status = status
    state.whenFinished()
}

export function getPretchedHtmlOr(destination, receive, ifNoPrefetchExists) {
    let uri = getUriStringFromUrlObject(destination)

    if (! prefetches[uri]) return ifNoPrefetchExists()

    if (prefetches[uri].finished) {
        let html = prefetches[uri].html
        let finalDestination = prefetches[uri].finalDestination
        let status = prefetches[uri].status

        delete prefetches[uri]

        return receive(html, finalDestination, status)
    } else {
        prefetches[uri].whenFinished = () => {
            let html = prefetches[uri].html
            let finalDestination = prefetches[uri].finalDestination
            let status = prefetches[uri].status

            delete prefetches[uri]

            receive(html, finalDestination, status)
        }

        // Someone is waiting on this in-flight prefetch. If it fails, they're
        // left hanging with no navigation at all, so send them down the normal
        // request path instead where a failure can be handled properly...
        prefetches[uri].whenFailed = ifNoPrefetchExists
    }
}

