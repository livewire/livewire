import { trigger } from "@/hooks"
import { createUrlObjectFromString, getUriStringFromUrlObject } from "./links"

export function fetchHtml(destination, callback) {
    let uri = getUriStringFromUrlObject(destination)

    performFetch(uri, (html, finalDestination) => {
        callback(html, finalDestination)
    })
}

export function performFetch(uri, callback) {
    let options = {
        headers: {
            'X-Livewire-Navigate': ''
        }
    }

    trigger('navigate.request', {
        url: uri,
        options,
    })

    let finalDestination

    fetch(uri, options).then(response => {
        let destination = createUrlObjectFromString(uri)
        finalDestination = createUrlObjectFromString(response.url)

        // If there was no redirect triggered by the URL that was fetched...
        if ((destination.pathname + destination.search) === (finalDestination.pathname + finalDestination.search)) {
            // Then let's cary over any "hash" entries on the URL.
            // We have to do this because hashes aren't sent to
            // the server by "fetch", so it needs to get added
            finalDestination.hash = destination.hash
        }

        return response.text()
    }).then(html => {
        callback(html, finalDestination)
    });
}
