import Message from './message.js'
import MessageRequest from './messageRequest.js'
import requestBus from './requestBus.js'
import { trigger } from '@/hooks'

class MessageBroker {
    messages = new Map()

    getMessage(component) {
        let message = this.messages.get(component.id)

        if (! message) {
            message = new Message(component)
            this.messages.set(component.id, message)
        }

        return message
    }

    addInterceptor(interceptor, component) {
        let message = this.getMessage(component)

        message.addInterceptor(interceptor)
    }

    addContext(component, context) {
        let message = this.getMessage(component)

        message.addContext(context)
    }

    pullContext(component) {
        let message = this.getMessage(component)
        
        return message.pullContext()
    }

    addAction(action) {
        let message = this.getMessage(action.component)

        let promise = new Promise((resolve) => {
            message.addAction(action, resolve)
        })

        this.send(message)

        return promise
    }

    send(message) {
        this.bufferMessageForFiveMs(message)
    }

    bufferMessageForFiveMs(message) {
        if (message.isBuffering() || message.isCancelled()) return

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
            if (message.isCancelled()) return
            
            message.prepare()
        })

        let requests = this.corraleMessagesIntoRequests(messages)

        trigger('message.pooled', { requests })

        this.sendRequests(requests)
    }

    corraleMessagesIntoRequests(messages) {
        let requests = new Set()

        for (let message of messages) {
            if (message.isCancelled()) continue

            let hasFoundRequest = false

            requests.forEach(request => {
                if (! hasFoundRequest && ! message.isolate) {
                    request.addMessage(message)

                    hasFoundRequest = true
                }
            })

            if (! hasFoundRequest) {
                let request = new MessageRequest()

                request.addMessage(message)

                requests.add(request)
            }
        }

        return requests
    }

    sendRequests(requests) {
        requests.forEach(request => {
            requestBus.add(request)
        })
    }
}

let instance = new MessageBroker()

export default instance
