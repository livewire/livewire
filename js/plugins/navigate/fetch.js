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
    // The failure is already handled by errorCallback, so swallow the rejection to avoid an unhandled promise rejection...
    sendNavigateRequest(uri, callback, errorCallback).catch(() => {})
}
