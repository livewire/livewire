import { trigger } from "@/hooks"

export function fetchHtml(destination, callback) {
    let uri = destination.pathname + destination.search

    let options = {}

    trigger('navigate.request', {
        url: uri,
        options,
    })

    doFetch(uri, options).then(i => i.text()).then(html => {
        callback(html)
    })
}

export function doFetch(uri, options = {}) {
    trigger('navigate.request', {
        url: uri,
        options,
    })

    return fetch(uri, options);
}
