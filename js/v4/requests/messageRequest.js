import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri } from '@/utils'
import { showHtmlModal } from '@/request/modal'
import Request from './request.js'
import { trigger, triggerAsync } from '@/hooks'

export default class MessageRequest extends Request {
    messages = new Set()
    finishProfile = null

    addMessage(message) {
        this.messages.add(message)
        message.request = this
    }

    deleteMessage(message) {
        this.messages.delete(message)
    }

    hasMessageFor(component) {
        return !! this.findMessageByComponent(component)
    }

    findMessageByComponent(component) {
        return Array.from(this.messages).find(message => message.component.id === component.id)
    }

    isEmpty() {
        return this.messages.size === 0
    }

    processCancellations(existingRequest) {
        if (existingRequest.constructor.name !== MessageRequest.name) return

        Array.from(existingRequest.messages).forEach(existingMessage => {
            existingMessage.processCancellations(this)
        })
    }

    cancelMessage(message) {
        this.deleteMessage(message)

        if (this.messages.size === 0) {
            this.cancel()
        }
    }

    async send() {
        let payload = {
            _token: getCsrfToken(),
            components: Array.from(this.messages, i => i.payload)
        }

        let options = {
            method: 'POST',
            body: JSON.stringify(payload),
            headers: {
                'Content-type': 'application/json',
                'X-Livewire': '1', // This '1' value means nothing, but it stops Cloudflare from stripping the header...
            },
            signal: this.controller.signal,
        }

        this.finishProfile = trigger('request.profile', options)

        let updateUri = getUpdateUri()

        trigger('request', {
            url: updateUri,
            options,
            payload: options.body,
            respond: i => this.respondCallbacks.push(i),
            succeed: i => this.succeedCallbacks.push(i),
            fail: i => this.errorCallbacks.push(i),
        })

        let response

        try {
            let fetchPromise = fetch(updateUri, options)

            this.messages.forEach(message => {
                message.afterSend()
            })

            response = await fetchPromise
        } catch (e) {
            this.finish()

            this.error(e)

            return
        }

        this.finish()

        let mutableObject = {
            status: response.status,
            response,
        }

        this.respond(mutableObject)

        response = mutableObject.response

        let content = await response.text()

        // Handle error response...
        if (! response.ok) {
            this.fail(response, content)

            return
        }

        this.redirectIfNeeded(response)

        await this.succeed(response, content)
    }

    redirectIfNeeded(response) {
        if (response.redirected) {
            window.location.href = response.url
        }
    }

    respond(mutableObject) {
        this.respondCallbacks.forEach(i => i(mutableObject))
    }

    async succeed(response, content) {
        /**
         * Sometimes a response will be prepended with html to render a dump, so we
         * will seperate the dump html from Livewire's JSON response content and
         * render the dump in a modal and allow Livewire to continue with the
         * request.
         */
        if (contentIsFromDump(content)) {
            let dump
            [dump, content] = splitDumpFromContent(content)

            showHtmlModal(dump)

            this.finishProfile({ content: '{}', failed: true })
        } else {
            this.finishProfile({ content, failed: false })
        }

        let { components, assets } = JSON.parse(content)

        await triggerAsync('payload.intercept', { components, assets })

        this.messages.forEach(message => {
            components.forEach(component => {
                let snapshot = JSON.parse(component.snapshot)
                if (snapshot.memo.id === message.component.id) {
                    message.succeed(component)
                }
            })
        })

        this.succeedCallbacks.forEach(i => i({ status: response.status, json: JSON.parse(content) }))
    }

    cancel() {
        this.messages.forEach(message => {
            message.cancel()
        })

        super.cancel()
    }

    // If something went wrong with the fetch (particularly
    // this would happen if the connection went offline)
    // fail with a 503 and allow Livewire to clean up
    error(e) {
        this.finishProfile({ content: '{}', failed: true })

        let preventDefault = false

        this.messages.forEach(message => {
            message.error(e)
        })

        this.errorCallbacks.forEach(i => i({
            status: 503,
            content: null,
            preventDefault: () => preventDefault = true,
        }))
    }

    fail(response, content) {
        this.finishProfile({ content: '{}', failed: true })

        let preventDefault = false

        this.messages.forEach(message => {
            message.fail(response, content)
        })

        this.errorCallbacks.forEach(i => i({
            status: response.status,
            content,
            preventDefault: () => preventDefault = true,
        }))

        if (preventDefault) return

        if (response.status === 419) {
            this.handlePageExpiry()
        }

        if (response.aborted) {
            return
        } else {
            return this.showFailureModal(content)
        }
    }

    handlePageExpiry() {
        confirm(
            'This page has expired.\nWould you like to refresh the page?'
        ) && window.location.reload()
    }

    showFailureModal(content) {
        let html = content

        showHtmlModal(html)
    }
}
