import Request from './request.js'
import { createUrlObjectFromString } from "@/plugins/navigate/links.js"
import { trigger } from "@/hooks"

export default class PageRequest extends Request {
    successCallbacks = []
    errorCallbacks = []

    constructor(uri) {
        super()

        this.uri = uri
    }

    addSuccessCallback(callback) {
        this.successCallbacks.push(callback)
    }

    addErrorCallback(callback) {
        this.errorCallbacks.push(callback)
    }

    shouldCancel() {
        return request => {
            return [
                'PageRequest',
                'UpdateRequest',
            ].includes(request.constructor.name)
        }
    }

    async send() {
        // console.log('sending page request', this.uri)
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

            // console.log('page request success', destination)

            this.successCallbacks.forEach(callback => callback(html, destination))

            // console.log('page request morphed')

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
