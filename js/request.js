import { reactive as r, effect as e, toRaw as tr, stop as s, pauseTracking, enableTracking } from '@vue/reactivity'
import { dataGet, dataSet, each, deeplyEqual, isObjecty, deepClone, diff, isObject } from '@/utils'
import { showHtmlModal } from './modal'
import { on, trigger } from '@/events'
import Alpine from 'alpinejs'
import { wireProperty } from './wire'

/**
 * The Alpine build will need to use it's own reactivity hooks,
 * so we'll declare these as variables rather than direct imports.
 */

/**
 * We'll store all our "synthetic" instances in a single lookup so that
 * we can pass around an identifier, rather than the actual instance.
 */
export let store = new Map

let uri = document.querySelector('[data-uri]').getAttribute('data-uri')

export async function callMethod(symbol, method, params) {
    let result = await requestMethodCall(symbol, method, params)

    return result
}

let requestTargetQueue = new Map

function requestMethodCall(symbol, method, params) {
    requestCommit(symbol)

    return new Promise((resolve, reject) => {
        let queue = requestTargetQueue.get(symbol)

        let path = ''

        queue.calls.push({
            path,
            method,
            params,
            handleReturn(value) {
                resolve(value)
            },
        })
    })
}

/**
 * The term "commit" here refers to anytime we're making a network
 * request, updating the server, and generating a new snapshot.
 * We're "requesting" a new commit rather than executing it
 * immediately, because we might want to batch multiple
 * simultaneus commits from other synthetic targets.
 */
export function requestCommit(symbol) {
    if (! requestTargetQueue.has(symbol)) {
        requestTargetQueue.set(symbol, {
            calls: [],
            receivers: [],
            resolvers: [],
            handleResponse() {
                this.resolvers.forEach(i => i())
            }
        })
    }

    triggerSend()

    return new Promise((resolve, reject) => {
        let queue = requestTargetQueue.get(symbol)

        queue.resolvers.push(resolve)
    })
}

let requestBufferTimeout

/**
 * This is sort of "debounce" so that multiple
 * network requests can be bundled together.
 */
function triggerSend() {
    if (requestBufferTimeout) return

    requestBufferTimeout = setTimeout(() => {
        sendMethodCall()

        requestBufferTimeout = undefined
    }, 5)
}

/**
 * This method prepares the network request payload and makes
 * the actual request to the server to update the target,
 * store a new snapshot, and handle any side effects.
 *
 * This method should fire the following events:
 * - request.prepare
 * - request
 */
async function sendMethodCall() {
    requestTargetQueue.forEach((request, symbol) => {
        let target = store.get(symbol)

        trigger('request.prepare', target)
    })

    let payload = []
    let successReceivers = []
    let failureReceivers = []

    requestTargetQueue.forEach((request, symbol) => {
        let target = store.get(symbol)

        let propertiesDiff = diff(target.canonical, target.ephemeral)

        let targetPaylaod = {
            snapshot: target.encodedSnapshot,
            updates: propertiesDiff,
            calls: request.calls.map(i => ({
                path: i.path,
                method: i.method,
                params: i.params,
            }))
        }

        payload.push(targetPaylaod)

        let finishTarget = trigger('request', target, targetPaylaod)

        failureReceivers.push(() => {
            let failed = true

            finishTarget(failed)
        })

        successReceivers.push((snapshot, effects) => {
            target.mergeNewSnapshot(snapshot, effects)

            processEffects(target)

            if (effects['returns']) {
                let returns = effects['returns']

                // Here we'll match up returned values with their method call handlers. We need to build up
                // two "stacks" of the same length and walk through them together to handle them properly...
                let returnHandlerStack = request.calls.map(({ handleReturn }) => (handleReturn))

                returnHandlerStack.forEach((handleReturn, index) => {
                    handleReturn(returns[index])
                })
            }

            finishTarget()

            request.handleResponse()
        })
    })

    requestTargetQueue.clear()

    let response = await fetch(uri, {
        method: 'POST',
        body: JSON.stringify({
            _token: getCsrfToken(),
            components: payload,
        }),
        headers: {
            'Content-type': 'application/json',
            'X-Synthetic': '',
        },
    })

    let succeed = async (responseContent) => {
        let response = JSON.parse(responseContent)

        for (let i = 0; i < response.length; i++) {
            let { snapshot, effects } = response[i];

            successReceivers[i](snapshot, effects)
        }
    }

    let fail = async () => {
        for (let i = 0; i < failureReceivers.length; i++) {
            failureReceivers[i]();
        }

        let failed = true
    }

    await handleResponse(response, succeed, fail)
}

/**
 * Post requests in Laravel require a csrf token to be passed
 * along with the payload. Here, we'll try and locate one.
 */
function getCsrfToken() {
    if (document.querySelector('[data-csrf]')) {
        return document.querySelector('[data-csrf]').getAttribute('data-csrf')
    }

    throw 'Livewire: No CSRF token detected'
}

/**
 * Here we'll take the new state and side effects from the
 * server and use them to update the existing data that
 * users interact with, triggering reactive effects.
 */

export function processEffects(target) {
    let effects = target.effects

    trigger('effects', target, effects)
}

export async function handleResponse(response, succeed, fail) {
    let content = await response.text()

    if (response.ok) {
        /**
         * Sometimes a redirect happens on the backend outside of Livewire's control,
         * for example to a login page from a middleware, so we will just redirect
         * to that page.
         */
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
            [dump, content] = splitDumpFromContent(content)

            showHtmlModal(dump)
        }

        return await succeed(content)
    }

    let skipDefault = false

    trigger('response.error', response, content, () => skipDefault = true)

    if (skipDefault) return await fail()

    if (response.status === 419) {
        handlePageExpiry()

        return await fail()
    }

    handleFailure(content)
    
    await fail()
}

function contentIsFromDump(content) {
    return !! content.match(/<script>Sfdump\(".+"\)<\/script>/)
}

function splitDumpFromContent(content) {
    let dump = content.match(/.*<script>Sfdump\(".+"\)<\/script>/s)
    return [dump, content.replace(dump, '')]
}

function handlePageExpiry() {
    confirm(
        'This page has expired.\nWould you like to refresh the page?'
    ) && window.location.reload()
}

function handleFailure(content) {
    let html = content

    showHtmlModal(html)
}