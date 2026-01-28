import { performFetch } from "@/plugins/navigate/fetch";
import { getUriStringFromUrlObject } from "./links";
import { storeCurrentPageStatus } from "./history";

// Warning: this could cause some memory leaks
let prefetches = {}

// Default prefetch cache duration is 30 seconds...
let cacheDuration = 30000

export function prefetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination)

    if (prefetches[uri]) return

    prefetches[uri] = { finished: false, html: null, whenFinished: () => setTimeout(() => delete prefetches[uri], cacheDuration) }

    performFetch(uri, (html, routedUri, status) => {
        storeCurrentPageStatus(status)

        callback(html, routedUri)
    }, () => {
        // If the fetch failed, remove the prefetch so it gets attempted again...
        delete prefetches[uri]

        errorCallback()
    })
}

export function storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination) {
    let state = prefetches[getUriStringFromUrlObject(destination)]
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
    }
}

