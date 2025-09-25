import { getUriStringFromUrlObject } from "./links"
import { sendNavigateRequest } from "@/request"

export function fetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination)

    performFetch(uri, (html, finalDestination) => {
        callback(html, finalDestination)
    }, errorCallback)
}

export function performFetch(uri, callback, errorCallback) {
    sendNavigateRequest(uri, callback, errorCallback)
}
