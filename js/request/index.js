import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri } from '@/utils'
import { coordinateNetworkInteractions } from './interactions.js'
import { MessageRequest, PageRequest } from './request.js'
import { InterceptorRegistry } from './interceptor.js'
import { trigger, triggerAsync } from '@/hooks.js'
import { showHtmlModal } from '@/utils/modal.js'
import { MessageBus, scopeSymbolFromMessage } from './messageBus.js'
import Message from './message.js'
import Action from './action.js'

let outstandingActionOrigin = null
let outstandingActionMetadata = {}
let interceptors = new InterceptorRegistry
let messageBus = new MessageBus()
let actionInterceptors = []
let partitionInterceptors = []

export function setNextActionOrigin(origin) {
    outstandingActionOrigin = origin
}

export function setNextActionMetadata(metadata) {
    outstandingActionMetadata = metadata
}

export function intercept(component, callback) {
    return interceptors.addInterceptor(component, callback)
}

export function interceptAction(callback) {
    actionInterceptors.push(callback)

    return () => {
        actionInterceptors.splice(actionInterceptors.indexOf(callback), 1)
    }
}

export function interceptPartition(callback) {
    partitionInterceptors.push(callback)

    return () => {
        partitionInterceptors.splice(partitionInterceptors.indexOf(callback), 1)
    }
}

export function interceptMessage(callback) {
    return interceptors.addMessageInterceptor(callback)
}

export function interceptRequest(callback) {
    return interceptors.addRequestInterceptor(callback)
}

interceptMessage(({ message, onFinish }) => {
    messageBus.addActiveMessage(message)

    onFinish(() => messageBus.removeActiveMessage(message))
})

// Ensure that other parts of the codebase are able to intercept actions before the default handling...
queueMicrotask(() => {
    coordinateNetworkInteractions(messageBus)
})

export function fireAction(component, method, params = [], metadata = {}) {
    if (component.__isWireProxy) component = component.__instance

    let action = constructAction(component, method, params, metadata)

    let prevented = false

    actionInterceptors.forEach(callback => {
        callback({
            action,
            reject: () => { action.rejectPromise(); prevented = true },
            defer: () => prevented = true,
        })
    })

    if (prevented) return action.promise

    return fireActionInstance(action)
}

export function constructAction(component, method, params, metadata) {
    let origin = outstandingActionOrigin

    outstandingActionOrigin = null

    metadata = {
        ...metadata,
        ...outstandingActionMetadata,
    }

    outstandingActionMetadata = {}

    return new Action(component, method, params, metadata, origin)
}

export function fireActionInstance(action) {
    let message = createOrAddToOutstandingMessage(action)

    messageBus.messageBuffer(message, () => {
        sendMessages()
    })

    return action.promise
}

export function createOrAddToOutstandingMessage(action) {
    let message = messageBus.findScopedPendingMessage(action)

    if (! message) message = new Message(action.component)

    message.addAction(action)

    messageBus.addPendingMessage(message)

    return message
}

function sendMessages() {
    let requests = new Set()

    messageBus.eachPendingMessage(message => {
        partitionInterceptors.forEach(callback => {
            callback({
                message,
                compileRequest: (messages) => {
                    if (Array.from(requests).some(request => Array.from(request.messages).some(message => messages.includes(message)))) {
                        throw new Error('A request already contains one of the messages in this array')
                    }

                    let request = new MessageRequest()

                    messages.forEach(message => request.addMessage(message))

                    requests.add(request)

                    return request
                },
            })
        })
    })

    let messages = messageBus.getPendingMessages()

    messageBus.clearPendingMessages()

    for (let message of messages) {
        if (Array.from(requests).some(request => request.messages.has(message))) {
            continue
        }

        let hasFoundRequest = false

        requests.forEach(request => {
            if (! hasFoundRequest) {
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

    requests.forEach(request => {
        request.messages.forEach(message => {
            message.snapshot = message.component.getEncodedSnapshotWithLatestChildrenMergedIn()
            message.updates = message.component.getUpdates()
            message.calls = Array.from(message.actions).map(i => ({
                method: i.method,
                params: i.params,
                metadata: i.metadata,
            }))

            message.payload = {
                snapshot: message.snapshot,
                updates: message.updates,
                // @todo: Rename to "actions"...
                calls: message.calls,
            }
        })
    })

    // Assign scope symbols to messages...
    requests.forEach(request => {
        request.messages.forEach(message => {
            message.scope = scopeSymbolFromMessage(message)
        })
    })

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

    requests.forEach(request => {
        request.initInterceptors(interceptors)

        if (request.hasAllCancelledMessages()) {
            request.abort()
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
            stream: async ({ response }) => {
                request.onStream({ response })

                let finalResponse = ''

                try {
                    finalResponse = await interceptStreamAndReturnFinalResponse(response, streamedJson => {
                        let componentId = streamedJson.id

                        request.messages.forEach(message => {
                            if (message.component.id === componentId) {
                                message.onStream({ streamedJson })
                            }
                        })

                        trigger('stream', streamedJson)
                    })
                } catch (e) {
                    request.abort()

                    throw e
                }

                return finalResponse
            },
            parsed: ({ response, responseBody }) => {
                request.onParsed({ response, responseBody })
            },
            error: ({ response, responseBody }) => {
                let preventDefault = false

                request.onError({ response, responseBody, preventDefault: () => preventDefault = true })

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

                request.onRedirect({ url, preventDefault: () => preventDefault = true })

                if (preventDefault) return

                window.location.href = url
            },
            dump: (content) => {
                let preventDefault = false

                request.onDump({ content, preventDefault: () => preventDefault = true })

                if (preventDefault) return

                showHtmlModal(content)
            },
            success: async ({ response, responseBody, responseJson }) => {
                request.onSuccess({ response, responseBody, responseJson })

                await triggerAsync('payload.intercept', responseJson)

                let messageResponsePayloads = responseJson.components

                request.messages.forEach(message => {
                    messageResponsePayloads.forEach(payload => {
                        if (message.isCancelled()) return

                        let { snapshot: snapshotEncoded, effects } = payload
                        let snapshot = JSON.parse(snapshotEncoded)

                        if (snapshot.memo.id === message.component.id) {
                            message.responsePayload = { snapshot, effects }

                            message.onSuccess()
                            if (message.isCancelled()) return

                            message.component.mergeNewSnapshot(snapshotEncoded, effects, message.updates)

                            message.onSync()
                            if (message.isCancelled()) return

                            // Trigger any side effects from the payload like "morph" and "dispatch event"...
                            message.component.processEffects(effects, request)

                            message.onEffect()
                            if (message.isCancelled()) return

                            queueMicrotask(() => {
                                if (message.isCancelled()) return

                                message.onMorph()

                                setTimeout(() => {
                                    if (message.isCancelled()) return

                                    message.onRender()
                                })
                            })
                        }
                    })
                })
            },
        })
    })
}

async function sendRequest(request, handlers) {
    let response

    try {
        if (request.isAborted()) return

        let responsePromise = fetch(request.uri, request.options)

        if (request.isAborted()) return
        handlers.send({ responsePromise })

        response = await responsePromise
    } catch (e) {
        if (request.isAborted()) return

        handlers.failure({ error: e })

        return
    }

    handlers.response({ response })

    let responseBody = null

    if (response.headers.has('X-Livewire-Stream')) {
        responseBody = await handlers.stream({ response })
    } else {
        responseBody = await response.text()
    }

    if (request.isAborted()) return

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

async function interceptStreamAndReturnFinalResponse(response, callback) {
    let reader = response.body.getReader()
    let remainingResponse = ''

    while (true) {
        let { done, value: chunk } = await reader.read()

        let decoder = new TextDecoder
        let output = decoder.decode(chunk)

        let [ streams, remaining ] = extractStreamObjects(remainingResponse + output)

        streams.forEach(stream => {
            callback(stream)
        })

        remainingResponse = remaining

        if (done) return remainingResponse
    }
}

function extractStreamObjects(raw) {
    let regex = /({"stream":true.*?"endStream":true})/g

    let matches = raw.match(regex)

    let parsed = []

    if (matches) {
        for (let i = 0; i < matches.length; i++) {
            parsed.push(JSON.parse(matches[i]).body)
        }
    }

    let remaining = raw.replace(regex, '');

    return [ parsed, remaining ];
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

        let destination = getDestination(uri, response)

        let html = await response.text()

        callback(html, destination)
    } catch (error) {
        errorCallback(error)

        throw error
    }
}

function getDestination(uri, response) {
    let destination = createUrlObjectFromString(uri)
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
    onFailure,
    onResponse,
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
    onCancel,
    onError,
    onSuccess,
    onFinish,
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

    onFinish(() => {
        respondCallbacks.forEach(callback => callback())
    })

    onSuccess(({ payload, onSync, onMorph, onRender }) => {
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
        failCallbacks.forEach(callback => callback())
    })
})