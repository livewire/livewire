import { getUriStringFromUrlObject } from "./links"
import { sendNavigateRequest } from "@/request"
import { storeCurrentPageStatus } from "./history"

export function fetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination)

    performFetch(uri, (html, finalDestination, status) => {
        storeCurrentPageStatus(status)

        callback(html, finalDestination)
    }, errorCallback)
}

export function performFetch(uri, callback, errorCallback) {
    // `errorCallback` has already dealt with this failure by the time the promise
    // rejects. Leaving it unobserved would surface it as an unhandled rejection,
    // which every error-tracking SDK files as an application error...
    sendNavigateRequest(uri, callback, errorCallback).catch(() => {})
}
