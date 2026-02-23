
export function hasQueryParam(param) {
    let queryParams = new URLSearchParams(window.location.search);

    return queryParams.has(param)
}

export function getQueryParam(param) {
    let queryParams = new URLSearchParams(window.location.search);

    return queryParams.get(param)
}

export function setQueryParam(param, value) {
    let queryParams = new URLSearchParams(window.location.search);

    queryParams.set(param, value)

    let url = urlFromQueryParams(queryParams)

    history.replaceState(history.state, '', url)
}

function urlFromQueryParams(queryParams) {
    let queryString = queryParams.toString()

    return window.location.origin
        + window.location.pathname
        + (queryString ? `?${queryString}` : '')
        + window.location.hash
}
