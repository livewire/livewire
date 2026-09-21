import { getUriStringFromUrlObject } from "./links"
import { sendNavigateRequest } from "@/request"
import { storeCurrentPageStatus } from "./history"
import { getNoncesFromCspHeaders, replaceNoncesInHtml } from "@/utils"

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
    sendNavigateRequest(uri, (html, destination, status, headers) => {
        // The browser still enforces the original page's CSP, so rewrite the nonces
        // this response's own CSP authorized to the original page's nonce. Any
        // others are left alone for the browser to deal with...
        html = replaceNoncesInHtml(html, getNoncesFromCspHeaders(headers))

        callback(html, destination, status)
    }, errorCallback).catch(() => {})
}
