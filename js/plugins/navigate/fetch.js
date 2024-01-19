import { trigger } from "@/hooks"

export function fetchHtml(destination, callback) {
    let uri = destination.pathname + destination.search

    performFetch(uri, html => {
        callback(html)
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

    fetch(uri, options).then(i => i.text()).then(html => {
        callback(html)
    });
}
