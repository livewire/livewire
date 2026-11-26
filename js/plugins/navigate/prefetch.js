import { performFetch } from "@/plugins/navigate/fetch";
import { getUriStringFromUrlObject } from "./links";
import { storeCurrentPageStatus } from "./history";
import { interceptRequest } from "@/request";

// Warning: this could cause some memory leaks
let prefetches = {}

// Default prefetch cache duration is 30 seconds...
let cacheDuration = 30000

// A Livewire request can change what a page renders (auth, session, etc.), so
// throw out anything prefetched before it. Navigations that are waiting on
// an in-flight prefetch are sent down the normal request path instead.
// Polls are background refreshes, so they leave prefetches alone...
interceptRequest(({ request, onSend }) => {
    onSend(() => {
        let actions = Array.from(request.messages).flatMap(message => Array.from(message.actions))

        if (actions.length && actions.every(action => action.metadata.type === 'poll')) return

        Object.values(prefetches).forEach(prefetch => prefetch.finished || prefetch.whenFailed())

        prefetches = {}
    })
})

export function prefetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination)

    if (prefetches[uri]) return

    let prefetch = { finished: false, html: null, whenFinished: () => setTimeout(() => isCurrent() && delete prefetches[uri], cacheDuration), whenFailed: () => {} }

    // The prefetch may have been thrown out by a Livewire request while in-flight...
    let isCurrent = () => prefetches[uri] === prefetch

    prefetches[uri] = prefetch

    performFetch(uri, (html, routedUri, status) => {
        if (! isCurrent()) return

        storeCurrentPageStatus(status)

        callback(html, routedUri)
    }, () => {
        if (! isCurrent()) return

        let whenFailed = prefetch.whenFailed

        // If the fetch failed, remove the prefetch so it gets attempted again...
        delete prefetches[uri]

        errorCallback()

        whenFailed()
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

        // Someone is waiting on this in-flight prefetch. If it fails, they're
        // left hanging with no navigation at all, so send them down the normal
        // request path instead where a failure can be handled properly...
        prefetches[uri].whenFailed = ifNoPrefetchExists
    }
}

