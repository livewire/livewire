import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri } from '@/utils'
import { coordinateNetworkInteractions } from './interactions.js'
import { MessageRequest, PageRequest } from './request.js'
import { InterceptorRegistry } from './interceptor.js'
import { trigger, triggerAsync } from '@/hooks.js'
import { showHtmlModal } from '@/utils/modal.js'
import { MessageBus, scopeSymbolFromMessage } from './messageBus.js'
import Message from './message.js'
import Action from './action.js'
import { reconstructHtmlDelta } from '@/htmlDelta'
import {
    createGzipBody,
    materializeRender,
    materializeSnapshotDelta,
} from '@/renderTransport'

let outstandingActionOrigin = null
let outstandingActionMetadata = {}
let outstandingActionInterceptors = []
let interceptors = new InterceptorRegistry
let messageBus = new MessageBus()
let actionInterceptors = []
let partitionInterceptors = []
let sessionExpired = false
let requestEncoder = new TextEncoder()

export function sessionIsExpired() {
    return sessionExpired
}

export function setNextActionOrigin(origin) {
    outstandingActionOrigin = origin
}

export function setNextActionMetadata(metadata) {
    outstandingActionMetadata = metadata
}

export function setNextActionInterceptor(callback) {
    outstandingActionInterceptors.push(callback)
}

export function interceptAction(callback) {
    actionInterceptors.push(callback)

    return () => {
        actionInterceptors.splice(actionInterceptors.indexOf(callback), 1)
    }
}

export function interceptMessage(callback) {
    return interceptors.addMessageInterceptor(callback)
}

export function interceptRequest(callback) {
    return interceptors.addRequestInterceptor(callback)
}

export function interceptPartition(callback) {
    partitionInterceptors.push(callback)

    return () => {
        partitionInterceptors.splice(partitionInterceptors.indexOf(callback), 1)
    }
}

// Component-scoped interceptors...

export function interceptComponentAction(component, actionNameOrCallback, maybeCallback) {
    let actionName = typeof actionNameOrCallback === 'string' ? actionNameOrCallback : null
    let callback = actionName ? maybeCallback : actionNameOrCallback

    return interceptAction(({ action, ...rest }) => {
        if (action.component !== component) return
        if (actionName && action.name !== actionName) return

        callback({ action, ...rest })
    })
}

export function interceptComponentMessage(component, actionNameOrCallback, maybeCallback) {
    let actionName = typeof actionNameOrCallback === 'string' ? actionNameOrCallback : null
    let callback = actionName ? maybeCallback : actionNameOrCallback

    return interceptors.addInterceptor(component, ({ message, ...rest }) => {
        if (actionName) {
            let hasAction = Array.from(message.actions).some(a => a.name === actionName)

            if (! hasAction) return
        }

        callback({ message, ...rest })
    })
}

export function interceptComponentRequest(component, actionNameOrCallback, maybeCallback) {
    let actionName = typeof actionNameOrCallback === 'string' ? actionNameOrCallback : null
    let callback = actionName ? maybeCallback : actionNameOrCallback

    return interceptRequest(({ request, ...rest }) => {
        let matchingMessages = Array.from(request.messages).filter(m => {
            if (m.component !== component) return false

            if (actionName) {
                return Array.from(m.actions).some(a => a.name === actionName)
            }

            return true
        })

        if (matchingMessages.length === 0) return

        callback({ request, ...rest })
    })
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

    actionInterceptors.forEach(callback => {
        callback({
            action,
            onSend: (cb) => action.onSendCallbacks.push(cb),
            onCancel: (cb) => action.onCancelCallbacks.push(cb),
            onSuccess: (cb) => action.onSuccessCallbacks.push(cb),
            onError: (cb) => action.onErrorCallbacks.push(cb),
            onFailure: (cb) => action.onFailureCallbacks.push(cb),
            onFinish: (cb) => action.onFinishCallbacks.push(cb),
        })
    })

    if (action.isCancelled() || action.isDeferred()) return action.promise

    return fireActionInstance(action)
}

export function constructAction(component, method, params, metadata) {
    let origin = outstandingActionOrigin
    let pendingInterceptors = outstandingActionInterceptors

    outstandingActionOrigin = null
    outstandingActionInterceptors = []

    metadata = {
        ...metadata,
        ...outstandingActionMetadata,
    }

    outstandingActionMetadata = {}

    let action = new Action(component, method, params, metadata, origin)

    // Set fire function to avoid circular dependency
    action._fire = fireActionInstance

    // Attach any per-action interceptors (from event.detail.livewire.interceptAction)
    pendingInterceptors.forEach(callback => action.addInterceptor(callback))

    return action
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
                    // When multiple components in a hierarchy listen to the same event and one
                    // has a modelable child, the partition interceptor may try to bundle the
                    // same child message into multiple requests. For example: Parent and Child
                    // both listen to 'foo', and Child has a modelable grandchild. Parent's
                    // interceptor bundles [Parent, Grandchild] into Request 1. Then Child's
                    // interceptor tries to bundle [Child, Grandchild], but Grandchild is already
                    // in Request 1. Rather than throwing an error, we merge Child into Request 1
                    // so all related components are sent together, maintaining data consistency.
                    let existingRequest = Array.from(requests).find(request =>
                        messages.some(message => request.messages.has(message))
                    )

                    if (existingRequest) {
                        // Add any new messages to the existing request
                        messages.forEach(message => {
                            if (!existingRequest.messages.has(message)) {
                                existingRequest.addMessage(message)
                            }
                        })
                        return existingRequest
                    }

                    // No overlap, create a new request
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
                // Don't add to a request that already has a message for the same component
                let hasMessageForSameComponent = Array.from(request.messages).some(m => m.component === message.component)

                if (hasMessageForSameComponent) return

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
            message.snapshotBaseline = message.snapshot
            message.renderBaseline = message.component.captureRenderBaseline?.() || null
            message.updates = message.component.getUpdates()
            message.calls = Array.from(message.actions).map(i => ({
                method: i.name,
                params: i.params,
                metadata: i.metadata,
            }))

            let render = message.component.getRenderMetadata?.(message.renderBaseline) || {}
            let allowsSnapshotReferences = render.capabilities?.includes('snapshot-ref') || false

            message.renderAttemptedPortable = render.chunks !== undefined
                || render.fragments !== undefined
            message.snapshotReference = message.component.captureSnapshotReference?.(
                allowsSnapshotReferences,
                message.snapshot,
            ) || null

            message.payload = {
                snapshot: message.snapshot,
                updates: message.updates,
                calls: message.calls,
            }

            if (Object.keys(render).length > 0) message.payload.render = render
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

        let cachedOptions = null

        Object.defineProperty(request, 'options', {
            get() {
                if (cachedOptions) return cachedOptions

                cachedOptions = {
                    method: 'POST',
                    body: JSON.stringify(buildNetworkPayload(request)),
                    headers: {
                        'Content-type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Livewire': '1', // This '1' value means nothing, but it stops Cloudflare from stripping the header...
                    },
                    signal: request.controller.signal,
                }

                return cachedOptions
            }
        })
    })

    requests.forEach(request => {
        request.initInterceptors(interceptors)

        if (request.hasAllCancelledMessages()) {
            request.cancel()
        }

        sendRequest(request, {
            send: ({ responsePromise }) => {
                request.invokeOnSend({ responsePromise })
            },
            failure: ({ error }) => {
                request.invokeOnFailure({ error })
            },
            finish: () => {
                request.invokeOnFinish()
            },
            response: ({ response }) => {
                request.invokeOnResponse({ response })
            },
            stream: async ({ response }) => {
                request.invokeOnStream({ response })

                let finalResponse = ''

                try {
                    finalResponse = await interceptStreamAndReturnFinalResponse(response, json => {
                        let componentId = json.id

                        request.messages.forEach(message => {
                            if (message.component.id === componentId) {
                                message.invokeOnStream({ json })
                            }
                        })

                        trigger('stream', json)
                    })
                } catch (e) {
                    request.cancel()

                    throw e
                }

                return finalResponse
            },
            parsed: ({ response, responseBody }) => {
                request.invokeOnParsed({ response, body: responseBody })
            },
            error: ({ response, responseBody }) => {
                let preventDefault = false

                request.invokeOnError({ response, body: responseBody, preventDefault: () => preventDefault = true })

                if (preventDefault) return

                if (response.status === 419) {
                    if (sessionExpired) return

                    sessionExpired = true

                    confirm(
                        'This page has expired.\nWould you like to refresh the page?'
                    ) && window.location.reload()

                    return
                }

                if (response.aborted) return

                showHtmlModal(responseBody)
            },
            redirect: (url) => {
                let preventDefault = false

                request.invokeOnRedirect({ url, preventDefault: () => preventDefault = true })

                if (preventDefault) return

                window.location.href = url
            },
            dump: (html) => {
                let preventDefault = false

                request.invokeOnDump({ html, preventDefault: () => preventDefault = true })

                if (preventDefault) return

                showHtmlModal(html)
            },
            success: async ({ response, responseBody, responseJson }) => {
                await materializeResponseTransport(request, responseJson)

                request.invokeOnSuccess({ response, body: responseBody, json: responseJson })

                await triggerAsync('payload.intercept', responseJson)

                let messageResponsePayloads = responseJson.components
                let pendingMessages = []

                request.messages.forEach(message => {
                    if (message.isCancelled()) return

                    let payload = findResponsePayload(message, messageResponsePayloads)

                    if (! payload) return

                    // Server skipped this child (unchanged reactive props)...
                    if (payload.skip) {
                        message.responsePayload = payload
                        message.markSkipped()
                        message.invokeOnSkipped()
                        message.resolveActionPromises([], [])
                        message.invokeOnFinish()

                        return
                    }

                    pendingMessages.push(processMessageResponse(message, payload, request))
                })

                await Promise.all(pendingMessages)
            },
        })
    })
}

function buildNetworkPayload(request, forceFullSnapshotIds = null) {
    if (forceFullSnapshotIds === null) {
        request.snapshotReferencesRetryable = snapshotReferenceFallbackFits(request)
        request.usedSnapshotReferenceIds = new Set()
    }

    return {
        _token: getCsrfToken(),
        components: Array.from(request.messages, message => {
            let payload = { ...message.payload }
            let forceFullSnapshot = forceFullSnapshotIds?.has(
                message.component.id,
            ) || false

            if (! forceFullSnapshot
                && request.snapshotReferencesRetryable !== false
                && message.snapshotReference !== null
            ) {
                delete payload.snapshot

                payload.id = message.component.id
                payload.snapshotRef = message.snapshotReference

                if (forceFullSnapshotIds === null) {
                    request.usedSnapshotReferenceIds.add(message.component.id)
                }
            }

            return payload
        }),
    }
}

function snapshotReferenceFallbackFits(request) {
    let referencedMessages = Array.from(request.messages).filter(message => {
        return message.snapshotReference !== null
    })

    if (referencedMessages.length === 0) return true

    let limits = referencedMessages
        .map(message => message.component.getMaximumRequestBytes?.() ?? null)
        .filter(limit => Number.isSafeInteger(limit) && limit >= 0)

    if (limits.length === 0) return true

    let fullPayload = {
        _token: getCsrfToken(),
        components: Array.from(request.messages, message => ({
            ...message.payload,
        })),
    }
    let fullBody = JSON.stringify(fullPayload)

    return requestEncoder.encode(fullBody).length <= Math.min(...limits)
}

async function materializeResponseTransport(request, responseJson) {
    if (! Array.isArray(responseJson.components)) {
        throw new Error('Invalid Livewire response payload')
    }

    let hasResponseCompressionNegotiation = responseJson.transport?.v === 1

    if (hasResponseCompressionNegotiation) {
        request.messages.forEach(message => {
            message.component.rememberRequestCompression?.(
                responseJson.transport.requestGzip,
            )
        })
    }

    for (let payload of responseJson.components) {
        if (payload.skip) continue

        let message = findMessageForPayload(request, payload)

        if (! message) continue

        if (payload.snapshotDelta !== undefined) {
            payload.snapshot = await materializeSnapshotDelta(
                payload.snapshotDelta,
                message.snapshotBaseline,
            )

            delete payload.snapshotDelta
        }

        let effects = payload.effects

        if (! effects || typeof effects !== 'object') continue

        if (effects.render !== undefined) {
            try {
                effects.html = await materializeRender(
                    effects.render,
                    effects.html ?? null,
                    message.renderBaseline,
                )

                if (! hasResponseCompressionNegotiation) {
                    message.component.rememberRequestCompression?.(
                        effects.render.requestGzip,
                    )
                }
            } catch (error) {
                if (message.isTransportRecovery()) throw error

                delete effects.html
                delete effects.htmlDelta

                effects.renderRecovery = true
            }

            continue
        }

        if (effects.htmlDelta !== undefined) {
            try {
                let baseline = message.renderBaseline
                let delta = effects.htmlDelta

                if (! baseline || baseline.hash !== delta.base) {
                    throw new Error('Livewire render baseline does not match its legacy descriptor')
                }

                effects.html = await reconstructHtmlDelta(
                    baseline.html,
                    delta.patches ?? delta.patch,
                    effects.htmlHash,
                )

                delete effects.htmlDelta
            } catch (error) {
                if (message.isTransportRecovery()) throw error

                delete effects.html
                delete effects.htmlDelta

                effects.renderRecovery = true
            }
        }
    }
}

function findMessageForPayload(request, payload) {
    let componentId = payload.id

    if (typeof componentId !== 'string' && typeof payload.snapshot === 'string') {
        try {
            componentId = JSON.parse(payload.snapshot).memo.id
        } catch (error) {}
    }

    return Array.from(request.messages).find(message => {
        return message.component.id === componentId
    })
}

function findResponsePayload(message, payloads) {
    return payloads.find(payload => {
        if (payload.id === message.component.id) return true
        if (typeof payload.snapshot !== 'string') return false

        try {
            return JSON.parse(payload.snapshot).memo.id === message.component.id
        } catch (error) {
            return false
        }
    })
}

async function processMessageResponse(message, payload, request) {
    let { snapshot: snapshotEncoded, effects } = payload
    let snapshot = JSON.parse(snapshotEncoded)

    if (snapshot.memo.id !== message.component.id) return

    message.responsePayload = { snapshot, effects }

    message.invokeOnSuccess()
    if (message.isCancelled()) return

    try {
        // Use Alpine.transaction to batch data updates and DOM morphing
        // This prevents effects from firing before the morph cleanup runs
        await Alpine.transaction(async () => {
            message.component.mergeNewSnapshot(snapshotEncoded, effects, message.updates)
            message.component.rememberSnapshotReference?.(payload.snapshotRef, snapshotEncoded)

            message.invokeOnSync()
            if (message.isCancelled()) return

            // Trigger any side effects from the payload like "morph" and "dispatch event"...
            message.component.processEffects(effects, request)

            message.invokeOnEffect()
            if (message.isCancelled()) return

            if (! effects.renderRecovery) await message.invokeOnMorph()
        })

        if (effects.renderRecovery && ! message.isCancelled()) {
            await message.invokeOnMorph()
        }
    } catch (error) {
        console.error(error)

        if (! message.isCancelled()) message.invokeOnFailure(error)

        return
    }

    // Resolve promises & finish AFTER morph completes
    if (! message.isCancelled()) {
        message.resolveActionPromises(
            message.pendingReturns,
            message.pendingReturnsMeta
        )
        message.invokeOnFinish()
    }

    requestAnimationFrame(() => {
        if (message.isCancelled()) return

        message.invokeOnRender()
    })
}

async function sendRequest(request, handlers) {
    let response

    try {
        if (request.isCancelled()) return

        let responsePromise = fetchWithSnapshotReferenceRetry(request)

        if (request.isCancelled()) return
        handlers.send({ responsePromise })

        response = await responsePromise
    } catch (e) {
        if (request.isCancelled()) return

        handlers.failure({ error: e })
        handlers.finish()

        return
    }

    handlers.response({ response })

    let responseBody = null

    if (response.headers.has('X-Livewire-Stream')) {
        responseBody = await handlers.stream({ response })
    } else {
        responseBody = await response.text()
    }

    if (request.isCancelled()) return

    handlers.parsed({ response, responseBody })

    // Handle error response...
    if (! response.ok) {
        handlers.error({ response, responseBody })
        handlers.finish()

        return
    }

    if (response.redirected) {
        handlers.redirect(response.url)
        handlers.finish()

        return
    }

    /**
     * Sometimes a response will be prepended with html to render a dump, so we
     * will separate the dump html from Livewire's JSON response content and
     * render the dump in a modal and allow Livewire to continue with the
     * request.
     */
    if (contentIsFromDump(responseBody)) {
        let dump

        [dump, responseBody] = splitDumpFromContent(responseBody)

        handlers.dump(dump)
    }

    let responseJson = null

    try {
        responseJson = JSON.parse(responseBody)
    } catch (e) {
        console.error(e)

        // When a stream is started, the headers are already sent,
        // so if an error occurs, the responseBody won't be JSON,
        // and we can treat it like an error response...
        handlers.error({ response, responseBody })
        handlers.finish()

        return
    }

    try {
        await handlers.success({ response, responseBody, responseJson })
    } catch (error) {
        if (! request.isCancelled()) handlers.failure({ error })

        handlers.finish()

        return
    }

    handlers.finish()
}

async function fetchWithSnapshotReferenceRetry(request) {
    await prepareRequestOptions(request)

    let response = await fetch(request.uri, request.options)
    let messagesWithReferences = Array.from(request.messages).filter(message => {
        return request.usedSnapshotReferenceIds?.has(message.component.id) || false
    })

    if (response.status !== 409
        || response.headers.get('X-Livewire-Snapshot-Missing') !== '1'
        || messagesWithReferences.length === 0
    ) return response

    let inspectionResponse = typeof response.clone === 'function'
        ? response.clone()
        : response
    let responseBody = await inspectionResponse.text()
    let missingIds = []

    try {
        let payload = JSON.parse(responseBody)

        if (Array.isArray(payload.snapshotMissing)) missingIds = payload.snapshotMissing
    } catch (error) {}

    let retryableMessages = messagesWithReferences.filter(message => {
        return missingIds.length === 0 || missingIds.includes(message.component.id)
    })

    if (retryableMessages.length === 0) return response

    retryableMessages.forEach(message => {
        message.component.rejectSnapshotReference?.(message.snapshotReference)
    })

    let referencedMessageIds = new Set(
        messagesWithReferences.map(message => message.component.id),
    )

    await prepareRequestOptions(request, referencedMessageIds)

    return await fetch(request.uri, request.options)
}

async function prepareRequestOptions(request, forceFullSnapshotIds = null) {
    let options = request.options
    let body = forceFullSnapshotIds !== null
        ? JSON.stringify(buildNetworkPayload(request, forceFullSnapshotIds))
        : options.body

    delete options.headers['Content-Encoding']

    options.body = body

    if (typeof body !== 'string') return

    let compressionThresholds = Array.from(request.messages, message => {
        return message.component.getRequestCompressionMinimumBytes?.() ?? null
    }).filter(value => Number.isSafeInteger(value) && value >= 0)

    if (compressionThresholds.length === 0) return

    let compressionThreshold = Math.min(...compressionThresholds)

    let bodyBytes = requestEncoder.encode(body).length

    if (bodyBytes < compressionThreshold) return

    let compressed = await createGzipBody(body)

    if (! compressed || compressed.length >= bodyBytes) return

    options.body = compressed
    options.headers['Content-Encoding'] = 'gzip'
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

        let status = response.status

        callback(html, destination, status)
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
        // Then let's carry over any "hash" entries on the URL.
        // We have to do this because hashes aren't sent to
        // the server by "fetch", so it needs to get added
        finalDestination.hash = destination.hash
    }

    return finalDestination
}

function createUrlObjectFromString(urlString) {
    return urlString !== null && new URL(urlString, document.baseURI)
}

// Load legacy event support ('request' and 'commit' events)
import { registerLegacyEventSupport } from './legacy.js'
registerLegacyEventSupport(interceptRequest, interceptMessage)
