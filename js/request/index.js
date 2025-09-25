import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri } from '@/utils'
import { InterceptorRegistry } from './interceptor.js'
import { trigger, triggerAsync } from '@/hooks.js'
import MessageRequest from './messageRequest.js'
import Message from './message.js'
import Action from './action.js'

let interceptors = new InterceptorRegistry
let outstandingActionOrigin = null
let outstandingMessages = new Map()
let requests = new Set()

export function setNextActionOrigin(origin) {
    outstandingActionOrigin = origin
}

export function intercept(callback, component = null, method = null) {
    return interceptors.add(callback, component, method)
}

export function fireAction(component, method, params = [], metadata = {}) {
    /**
     * Construct a Action object
     */
    let origin = outstandingActionOrigin

    outstandingActionOrigin = null

    origin = origin || {}

    let action = new Action(component, method, params, metadata, origin)

    /**
     * Find or construct a Message object
     */
    let message = outstandingMessages.get(component)

    if (! message) {
        message = new Message(component)

        outstandingMessages.set(component, message)
    }

    message.addAction(action)

    return new Promise((resolve) => {
        if (message.isBuffering() || message.isCancelled()) return

        message.buffer()

        setTimeout(() => { // Buffer for 5ms to allow other areas of the codebase to hook into the lifecycle of an individual commit...
            trigger('message.pooling', { messages: outstandingMessages })

            let pooledMessages = new Set(outstandingMessages.values())

            outstandingMessages.clear()

            if (pooledMessages.size === 0) return

            pooledMessages.forEach(message => {
                if (message.isCancelled()) return

                message.prepare()
            })

            let pooledRequests = new Set()

            for (let message of pooledMessages) {
                if (message.isCancelled()) continue

                let hasFoundRequest = false

                pooledRequests.forEach(request => {
                    if (! hasFoundRequest && ! message.isolate) {
                        request.addMessage(message)

                        hasFoundRequest = true
                    }
                })

                if (! hasFoundRequest) {
                    let request = new MessageRequest()

                    request.addMessage(message)

                    pooledRequests.add(request)
                }
            }

            trigger('message.pooled', { requests: pooledRequests })

            pooledRequests.forEach(async request => {
                requests.add(request)

                let payload = {
                    _token: getCsrfToken(),
                    components: Array.from(request.messages, i => i.payload)
                }

                let options = {
                    method: 'POST',
                    body: JSON.stringify(payload),
                    headers: {
                        'Content-type': 'application/json',
                        'X-Livewire': '1', // This '1' value means nothing, but it stops Cloudflare from stripping the header...
                    },
                    signal: request.controller.signal,
                }

                let updateUri = getUpdateUri()

                trigger('request', {
                    url: updateUri,
                    options,
                    payload: options.body,
                    respond: i => request.respondCallbacks.push(i),
                    succeed: i => request.succeedCallbacks.push(i),
                    fail: i => request.errorCallbacks.push(i),
                })

                let response

                try {
                    let fetchPromise = fetch(updateUri, options)

                    request.messages.forEach(message => {
                        message.afterSend()
                    })

                    response = await fetchPromise
                } catch (e) {
                    requests.delete(request)

                    request.messages.forEach(message => {
                        message.error(e)
                    })

                    request.errorCallbacks.forEach(i => i({
                        status: 503,
                        content: null,
                        preventDefault: () => {},
                    }))

                    return
                }

                requests.delete(request)

                let mutableObject = {
                    status: response.status,
                    response,
                }

                request.respondCallbacks.forEach(i => i(mutableObject))

                response = mutableObject.response

                let content = await response.text()

                // Handle error response...
                if (! response.ok) {
                    let preventDefault = false

                    request.messages.forEach(message => {
                        message.fail(response, content)
                    })

                    request.errorCallbacks.forEach(i => i({
                        status: response.status,
                        content,
                        preventDefault: () => preventDefault = true,
                    }))

                    if (preventDefault) return

                    if (response.status === 419) {
                        confirm(
                            'This page has expired.\nWould you like to refresh the page?'
                        ) && window.location.reload()
                    }

                    if (response.aborted) {
                        return
                    }

                    return showHtmlModal(content)
                }

                if (response.redirected) {
                    window.location.href = response.url
                }

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
                }

                let { components, assets } = JSON.parse(content)

                await triggerAsync('payload.intercept', { components, assets })

                request.messages.forEach(message => {
                    components.forEach(component => {
                        let snapshot = JSON.parse(component.snapshot)
                        if (snapshot.memo.id === message.component.id) {
                            message.succeed(component)
                        }
                    })
                })

                request.succeedCallbacks.forEach(i => i({ status: response.status, json: JSON.parse(content) }))
            })
        }, 5)
    })
}

function bufferMessageForFiveMs(message, callback) {

}