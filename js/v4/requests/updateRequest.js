import { getCsrfToken, getUpdateUri } from '@/utils'
import requestManager from './requestManager.js'

export default class UpdateRequest {
    messages = new Set()
    controller = new AbortController()

    addMessage(message) {
        this.messages.add(message)
        message.request = this
    }

    cancelIfItShouldBeCancelled() {
        if (this.allMessagesAreCancelled()) {
            this.cancel()
        }
    }

    allMessagesAreCancelled() {
        return Array.from(this.messages).every(message => message.isCancelled())
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

        let updateUri = getUpdateUri()

        let response
        
        try {
            response = await fetch(updateUri, options)
        } catch (e) {
            console.log('error', e)
            return
        }

        let content = await response.text()

        let { components, assets } = JSON.parse(content)

        this.succeed(components)
    }

    succeed(components) {
        this.messages.forEach(message => {
            components.forEach(component => {
                let snapshot = JSON.parse(component.snapshot)
                if (snapshot.memo.id === message.component.id) {
                    message.succeed(component)
                }
            })
        })
    }

    cancel() {
        this.controller.abort('cancelled')

        requestManager.remove(this)
    }
}