import { trigger } from "@/hooks"
import { createUrlObjectFromString, getUriStringFromUrlObject } from "./links"
import requestBus from "@/request/requestBus"
import PageRequest from "@/request/pageRequest"

export function fetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination)

    performFetch(uri, (html, finalDestination) => {
        callback(html, finalDestination)
    }, errorCallback)
}

export function performFetch(uri, callback, errorCallback) {
    let request = new PageRequest(uri)

    request.addSucceedCallback(callback)

    request.addErrorCallback(errorCallback)

    requestBus.add(request)
}
