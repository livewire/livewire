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
    });
}
