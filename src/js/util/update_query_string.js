import queryString from 'query-string'

export function updateQueryString(data) {
    const parsed = queryString.parse(document.location.search)

    const parsedAndMerged = {...parsed, ...data}

    const stringifiedWithMerge = queryString.stringify(parsedAndMerged)

    if (history.replaceState) {
        const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + stringifiedWithMerge;

        if (window.Turbolinks) {
            Turbolinks.controller.pushHistoryWithLocationAndRestorationIdentifier(newurl, Turbolinks.uuid())
        } else {
            window.history.pushState({...window.history.state, ...{ livewirePath: newurl }}, '', newurl);
        }
    }
}
