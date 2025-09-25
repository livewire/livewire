import Request from './request.js'
import MessageRequest from './messageRequest.js'
import { createUrlObjectFromString } from "@/plugins/navigate/links.js"
import { trigger } from "@/hooks"

export default class PageRequest extends Request {
    constructor(uri) {
        super()

        this.uri = uri
    }

    processCancellations(existingRequest) {
        let requestTypesToCancel = [
            PageRequest.name,
            MessageRequest.name,
        ]

        if (requestTypesToCancel.includes(existingRequest.constructor.name)) {
            existingRequest.cancel()
        }
    }

    async send() {
        let options = {
            // method: 'GET',
            headers: {
                'X-Livewire-Navigate': '1', // This '1' value means nothing, but it stops Cloudflare from stripping the header...
            },
            signal: this.controller.signal,
        }

        trigger('navigate.request', {
            url: this.uri,
            options,
        })

        try {
            let response = await fetch(this.uri, options)

            let destination = this.getDestination(response)

            let html = await response.text()

            this.succeedCallbacks.forEach(callback => callback(html, destination))
        } catch (error) {
            this.errorCallbacks.forEach(callback => callback(error))

            throw error
        }
    }

    getDestination(response) {
        let destination = createUrlObjectFromString(this.uri)
        let finalDestination = createUrlObjectFromString(response.url)

        // If there was no redirect triggered by the URL that was fetched...
        if ((destination.pathname + destination.search) === (finalDestination.pathname + finalDestination.search)) {
            // Then let's cary over any "hash" entries on the URL.
            // We have to do this because hashes aren't sent to
            // the server by "fetch", so it needs to get added
            finalDestination.hash = destination.hash
        }

        return finalDestination
    }
}
