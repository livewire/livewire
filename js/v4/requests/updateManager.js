import ComponentMessage from './componentMessage.js'
import UpdateRequest from './updateRequest.js'
import requestManager from './requestManager.js'

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
        let messages = new Set(this.messages.values())

        this.messages.clear()

        if (messages.size === 0) return

        messages.forEach(message => {
            message.prepare()
        })

        this.corraleMessagesIntoRequests(messages)
    }

    corraleMessagesIntoRequests(messages) {
        // @todo: Add isolation support...
        // let requests = new Set()

        let request = new UpdateRequest()

        for (let message of messages) {
            request.addMessage(message)
        }

        requestManager.add(request)
    }

    findMessageForComponentAlreadyInARequest(component) {
        for (let request of requestManager.requests) {
            if (! (request instanceof UpdateRequest)) continue

            for (let message of request.messages) {
                if (message.component.id === component.id) return message
            }
        }

        return null
    }
}

let instance = new UpdateManager()

export default instance
