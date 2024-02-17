import { trigger } from "@/hooks"
import { createUrlObjectFromString } from "./links"

export function fetchHtml(destination, callback) {
    let uri = destination.pathname + destination.search

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
        finalDestination = createUrlObjectFromString(response.url)

        return response.text()
    }).then(html => {
        callback(html, finalDestination)
    }).catch(error => {
        // Should we do anything here too? or just add this comment that is used elsewhere?
        // Should anything be shown to the user maybe?

            // Let's eat any promise rejects so that we don't
            // break the rest of Livewire's handling of the response...
    });
}
