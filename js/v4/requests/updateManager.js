import ComponentMessage from './componentMessage.js'
import UpdateRequest from './updateRequest.js'
import requestManager from './requestManager.js'
import { trigger } from '@/hooks'

class UpdateManager {
    messages = new Map()

    getMessage(component) {
        let message = this.messages.get(component.id)

        if (! message) {
            message = new ComponentMessage(component)
            this.messages.set(component.id, message)
        }

        return message
    }

    addUpdate(component) {
        let message = this.getMessage(component)

        let promise = new Promise((resolve) => {
            message.addResolver(resolve)
        })

        this.send(message)

        return promise
    }

    addCall(component, method, params) {
        let message = this.getMessage(component)

        let promise = new Promise((resolve) => {
            message.addCall(method, params, resolve)
        })

        this.send(message)

        return promise
    }

    send(message) {
        this.bufferMessageForFiveMs(message)
    }

    bufferMessageForFiveMs(message) {
        if (message.isBuffering()) return

        message.buffer()

        setTimeout(() => {
            this.prepareRequests()
        }, 5)
    }

    prepareRequests() {
        // Allow features like "reactive properties" to initiate associated
        // commits before those commits are pooled for a network request...
        trigger('message.pooling', { messages: this.messages })

        let messages = new Set(this.messages.values())

        this.messages.clear()

        if (messages.size === 0) return

        messages.forEach(message => {
            message.prepare()
        })

        let requests = this.corraleMessagesIntoRequests(messages)

        trigger('message.pooled', { requests })

        this.sendRequests(requests)
    }

    corraleMessagesIntoRequests(messages) {
        // @todo: Add isolation support...        
        let requests = new Set()

        let request = new UpdateRequest()

        for (let message of messages) {
            request.addMessage(message)
        }

        requests.add(request)

        return requests
    }

    sendRequests(requests) {
        requests.forEach(request => {
            requestManager.add(request)
        })
    }
}

let instance = new UpdateManager()

export default instance
