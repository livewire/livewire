import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri, WeakBag } from '@/utils'
import { MessageRequest, PageRequest } from './request.js'
import { InterceptorRegistry } from './interceptor.js'
import { trigger, triggerAsync } from '@/hooks.js'
import { showHtmlModal } from '@/utils/modal.js'
import Message from './message.js'
import Action from './action.js'
import { morph } from '@/morph'

let outstandingActionOrigin = null
let outstandingMessages = new Map
let interceptors = new InterceptorRegistry

export function setNextActionOrigin(origin) {
    outstandingActionOrigin = origin
}

export function intercept(component, callback) {
    interceptors.addInterceptor(component, callback)
}

export function interceptMessage(callback) {
    interceptors.addMessageInterceptor(callback)
}

export function interceptRequest(callback) {
    interceptors.addRequestInterceptor(callback)
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
            let messages = new Set(outstandingMessages.values())

            outstandingMessages.clear()

            prepareMessages(messages)

            let requests = createRequestsFromMessages(messages)

            requests.forEach(request => {
                request.initInterceptors(interceptors)

                if (request.hasAllCancelledMessages()) {
                    request.cancel()

                    return
                }

                sendRequest(request, {
                    send: ({ responsePromise }) => {
                        request.onSend({ responsePromise })
                    },
                    failure: ({ error }) => {
                        request.onFailure({ error })
                    },
                    response: ({ response }) => {
                        request.onResponse({ response })
                    },
                    parsed: ({ response, responseBody }) => {
                        request.onParsed({ response, responseBody })
                    },
                    error: ({ response, responseBody }) => {
                        let preventDefault = false

                        request.onError({ response, responseBody, preventDefault })

                        if (preventDefault) return

                        if (response.status === 419) {
                            confirm(
                                'This page has expired.\nWould you like to refresh the page?'
                            ) && window.location.reload()
                        }

                        if (response.aborted) return

                        showHtmlModal(responseBody)
                    },
                    redirect: (url) => {
                        let preventDefault = false

                        request.onRedirect({ url, preventDefault })

                        if (preventDefault) return

                        window.location.href = url
                    },
                    dump: (content) => {
                        let preventDefault = false

                        request.onDump({ content, preventDefault })

                        if (preventDefault) return

                        showHtmlModal(dumpContent)
                    },
                    success: async ({ response, responseBody, responseJson }) => {
                        request.onSuccess({ response, responseBody, responseJson })

                        await triggerAsync('payload.intercept', responseJson)

                        let messageResponsePayloads = responseJson.components

                        request.messages.forEach(message => {
                            messageResponsePayloads.forEach(payload => {
                                let { snapshot: snapshotEncoded, effects } = payload
                                let snapshot = JSON.parse(snapshotEncoded)

                                if (snapshot.memo.id === message.component.id) {
                                    message.responsePayload = { snapshot, effects }

                                    message.onSuccess()

                                    message.component.mergeNewSnapshot(snapshotEncoded, effects, message.updates)

                                    message.onSync()

                                    // Trigger any side effects from the payload like "morph" and "dispatch event"...
                                    message.component.processEffects(effects)

                                    let html = effects['html']

                                    queueMicrotask(() => {
                                        if (html) {
                                            applyMorph(message, html)

                                            message.onMorph()
                                        }

                                        setTimeout(() => {
                                            message.onRender()
                                        })
                                    })
                                }
                            })
                        })
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

    requests.forEach(request => {
        request.uri = getUpdateUri()

        Object.defineProperty(request, 'payload', {
            get() {
                return {
                    _token: getCsrfToken(),
                    components: Array.from(request.messages, i => i.payload)
                }
            }
        })

        Object.defineProperty(request, 'options', {
            get() {
                return {
                    method: 'POST',
                    body: JSON.stringify(request.payload),
                    headers: {
                        'Content-type': 'application/json',
                        'X-Livewire': '1', // This '1' value means nothing, but it stops Cloudflare from stripping the header...
                    },
                    signal: request.controller.signal,
                }
            }
        })
    })

    return requests
}

async function sendRequest(request, handlers) {
    let response

    try {
        let responsePromise = fetch(request.uri, request.options)

        handlers.send({ responsePromise })

        response = await responsePromise
    } catch (e) {
        handlers.failure({ error: e })

        return
    }

    handlers.response({ response })

    let responseBody = await response.text()

    handlers.parsed({ response, responseBody })

    // Handle error response...
    if (! response.ok) {
        handlers.error({ response, responseBody })

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
    if (contentIsFromDump(responseBody)) {
        let dump

        [dump, responseBody] = splitDumpFromContent(responseBody)

        handlers.dump(dump)
    }

    let responseJson = JSON.parse(responseBody)

    handlers.success({ response, responseBody, responseJson })
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

// Support legacy 'request' event...
interceptRequest(({
    request,
    onSend,
    onCancel,
    onFailure,
    onResponse,
    onParsed,
    onError,
    onSuccess,
}) => {
    let respondCallbacks = []
    let succeedCallbacks = []
    let failCallbacks = []

    trigger('request', {
        url: request.uri,
        options: request.options,
        payload: request.options.body,
        respond: i => respondCallbacks.push(i),
        succeed: i => succeedCallbacks.push(i),
        fail: i => failCallbacks.push(i),
    })

    onResponse(({ response }) => {
        respondCallbacks.forEach(callback => callback({
            status: response.status,
            response,
        }))
    })

    onSuccess(({ response, responseJson }) => {
        succeedCallbacks.forEach(callback => callback({
            status: response.status,
            json: responseJson,
        }))
    })

    onFailure(({ error }) => {
        failCallbacks.forEach(callback => callback({
            status: 503,
            content: null,
            preventDefault: () => {},
        }))
    })

    onError(({ response, responseBody, preventDefault }) => {
        failCallbacks.forEach(callback => callback({
            status: response.status,
            content: responseBody,
            preventDefault,
        }))
    })
})

// Support legacy 'commit' event...
interceptMessage(({
    message,
    onSend,
    onCancel,
    onError,
    onSuccess,
    onSync,
    onMorph,
    onRender,
}) => {
    // Allow other areas of the codebase to hook into the lifecycle
    // of an individual commit...
    let respondCallbacks = []
    let succeedCallbacks = []
    let failCallbacks = []

    trigger('commit', {
        component: message.component,
        commit: message.payload,
        respond: (callback) => {
            respondCallbacks.push(callback)
        },
        succeed: (callback) => {
            succeedCallbacks.push(callback)
        },
        fail: (callback) => {
            failCallbacks.push(callback)
        },
    })

    onSuccess(({ payload, onSync, onMorph, onRender }) => {
        respondCallbacks.forEach(callback => callback())

        onRender(() => {
            succeedCallbacks.forEach(callback => callback({
                snapshot: payload.snapshot,
                effects: payload.effects,
            }))
        })
    })

    onError(() => {
        failCallbacks.forEach(callback => callback())
    })

    onCancel(() => {
        respondCallbacks.forEach(callback => callback())
        failCallbacks.forEach(callback => callback())
    })
})