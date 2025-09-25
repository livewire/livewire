import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri } from '@/utils'
import { showHtmlModal } from '@/utils/modal'
import Request from './request.js'
import { trigger, triggerAsync } from '@/hooks'

export default class MessageRequest extends Request {
    messages = new Set()

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

    redirectIfNeeded(response) {

    }

    respond(mutableObject) {
        this.respondCallbacks.forEach(i => i(mutableObject))
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
