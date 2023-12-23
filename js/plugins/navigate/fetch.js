
export function fetchHtml(destination, callback, options = {}) {
    let uri = destination.pathname + destination.search

    fetch(uri, options).then(i => i.text()).then(html => {
        callback(html)
    })
}
