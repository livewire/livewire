import { performFetch } from "@/plugins/navigate/fetch";

// Warning: this could cause some memory leaks
let prefetches = {}

export function prefetchHtml(destination, callback) {
    let path = destination.pathname

    if (prefetches[path]) return

    prefetches[path] = { finished: false, html: null, whenFinished: () => {} }

    performFetch(path, (html, routedUri) => {
        callback(html, routedUri)
    })
}

export function storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination) {
    let state = prefetches[destination.pathname]
    state.html = html
    state.finished = true
    state.finalDestination = finalDestination
    state.whenFinished()
}

export function getPretchedHtmlOr(destination, receive, ifNoPrefetchExists) {
    let uri = destination.pathname + destination.search

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

