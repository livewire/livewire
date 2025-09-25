import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri } from '@/utils'
import { MessageRequest, PageRequest } from './request.js'
import { InterceptorRegistry } from './interceptor.js'
import { trigger, triggerAsync } from '@/hooks.js'
import { showHtmlModal } from '@/utils/modal.js'
import Message from './message.js'
import Action from './action.js'
import { morph } from '@/morph'

let interceptors = new InterceptorRegistry
let outstandingActionOrigin = null
let outstandingMessages = new Map()

export function setNextActionOrigin(origin) {
    outstandingActionOrigin = origin
}

export function intercept(callback, component = null, method = null) {
    return interceptors.add(callback, component, method)
}

export function fireAction(component, method, params = [], metadata = {}) {
    let origin = outstandingActionOrigin

    outstandingActionOrigin = null

    origin = origin || {}

    let action = new Action(component, method, params, metadata, origin)

    let message = outstandingMessages.get(component)

    if (! message) {
        message = new Message(component)

        outstandingMessages.set(component, message)

        setTimeout(() => { // Buffer for 5ms to allow other areas of the codebase to hook into the lifecycle of an individual commit...
            let messages = flushOutstandingMessages()

            prepareMessages(messages)

            let requests = createRequestsFromMessages(messages)

            requests.forEach(request => {
                sendRequest(request, {
                    failure: () => { // This is called when the request fails at the network level...
                        request.fail(503, null, () => {})
                    },
                    response: ({ status, response }) => { // This is called when the response is received...
                        request.respond(status, response)
                    },
                    error: ({ status, aborted, responseContent }) => { // This is called when the response fails at the HTTP level...
                        let preventDefault = false

                        request.fail(status, responseContent, () => preventDefault = true)

                        if (preventDefault) return

                        if (status === 419) {
                            confirm(
                                'This page has expired.\nWould you like to refresh the page?'
                            ) && window.location.reload()
                        }

                        if (aborted) return

                        showHtmlModal(responseContent)
                    },
                    redirect: (url) => { // This is called when the response is a redirect...
                        window.location.href = url
                    },
                    dump: (dumpContent) => { // This is called when the response is a dump...
                        showHtmlModal(dumpContent)
                    },
                    success: async ({ status, responseJson }) => { // This is called when the request fully succeeds...
                        await triggerAsync('payload.intercept', responseJson)

                        let messageResponsePayloads = responseJson.components

                        request.messages.forEach(message => {
                            messageResponsePayloads.forEach(payload => {
                                let { snapshot: snapshotEncoded, effects } = payload
                                let snapshot = JSON.parse(snapshotEncoded)

                                if (snapshot.memo.id === message.component.id) {
                                    message.responsePayload = { snapshot, effects }

                                    message.component.mergeNewSnapshot(snapshotEncoded, effects, message.updates)

                                    // Trigger any side effects from the payload like "morph" and "dispatch event"...
                                    message.component.processEffects(effects)

                                    let html = effects['html']

                                    let islands = effects['islands']

                                    if (! html && ! islands) {
                                        setTimeout(() => {
                                            // message.interceptors.forEach(i => i.returned())
                                        })

                                        return
                                    }

                                    queueMicrotask(() => {
                                        if (html) {
                                            applyMorph(message, html)
                                        }

                                        setTimeout(() => {
                                            // message.interceptors.forEach(i => i.afterRender({ component: message.component }))

                                            // message.interceptors.forEach(i => i.returned())
                                        })
                                    })
                                }
                            })
                        })

                        request.succeed(status, responseJson)
                    },
                })
            })
        }, 5)
    }

    let promiseResolver

    let promise = new Promise((resolve, reject) => {
        promiseResolver = { resolve, reject }
    })

    message.addAction(action, promiseResolver)

    return promise
}

function flushOutstandingMessages() {
    let messages = new Set(outstandingMessages.values())

    outstandingMessages.clear()

    return messages
}

function prepareMessages(messages) {
    trigger('message.pooling', { messages })

    messages.forEach(message => {
        trigger('commit.prepare', { component: message.component })

        message.snapshot = message.component.getEncodedSnapshotWithLatestChildrenMergedIn()
        message.updates = message.component.getUpdates()
        message.calls = message.actions.map(i => ({
            method: i.method,
            params: i.params,
            context: i.metadata,
        }))

        message.payload = {
            snapshot: message.snapshot,
            updates: message.updates,
            // @todo: Rename to "actions"...
            calls: message.calls,
        }

        // Allow other areas of the codebase to hook into the lifecycle
        // of an individual commit...
        trigger('commit', {
            component: message.component,
            commit: message.payload,
            respond: (callback) => {
                message.respondCallbacks.push(callback)
            },
            succeed: (callback) => {
                message.succeedCallbacks.push(callback)
            },
            fail: (callback) => {
                message.failCallbacks.push(callback)
            },
        })
    })
}

function createRequestsFromMessages(messages) {
    let requests = new Set()

    for (let message of messages) {
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

    trigger('message.pooled', { requests })

    return requests
}

async function sendRequest(request, handlers) {
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
        fail: i => request.failCallbacks.push(i),
    })

    let response

    try {
        let fetchPromise = fetch(updateUri, options)

        response = await fetchPromise
    } catch (e) {
        handlers.failure()

        return
    }

    let mutableResponseObject = {
        status: response.status,
        response,
    }

    handlers.response(mutableResponseObject)

    response = mutableResponseObject.response

    let responseContent = await response.text()

    // Handle error response...
    if (! response.ok) {
        handlers.error({ status: response.status, aborted: response.aborted, responseContent })

        return
    }

    if (response.redirected) {
        handlers.redirect(response.url)
    }

    /**
     * Sometimes a response will be prepended with html to render a dump, so we
     * will seperate the dump html from Livewire's JSON response content and
     * render the dump in a modal and allow Livewire to continue with the
     * request.
     */
    if (contentIsFromDump(responseContent)) {
        let dump

        [dump, responseContent] = splitDumpFromContent(responseContent)

        handlers.dump(dump)
    }

    let responseJson = JSON.parse(responseContent)

    handlers.success({ status: response.status, responseJson })
}

function applyMorph(message, html) {
    // check if testing environment and skip...
    if (process.env.NODE_ENV === 'test') return

    morph(message.component, message.component.el, html)
}

export async function sendNavigateRequest(uri, callback, errorCallback) {
    let request = new PageRequest(uri)

    let options = {
        // method: 'GET',
        headers: {
            'X-Livewire-Navigate': '1', // This '1' value means nothing, but it stops Cloudflare from stripping the header...
        },
        signal: request.controller.signal,
    }

    trigger('navigate.request', {
        uri,
        options,
    })

    let response

    try {
        response = await fetch(uri, options)

        let destination = getDestination(response)

        let html = await response.text()

        callback(html, destination)
    } catch (error) {
        errorCallback(error)

        throw error
    }
}

function getDestination(response) {
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

function createUrlObjectFromString(urlString) {
    return urlString !== null && new URL(urlString, document.baseURI)
}