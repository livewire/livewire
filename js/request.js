import { getCsrfToken, contentIsFromDump, splitDumpFromContent } from '@/utils'
import { showHtmlModal } from './modal'
import { trigger } from '@/events'
import { getCommits, flushCommits } from './commit'

/**
 * Livewire's update URI. This is configurable via Livewire::setUpdateRoute(...)
 */
let updateUri = document.querySelector('[data-uri]').getAttribute('data-uri')

export function triggerSend() {
    bundleMultipleRequestsTogetherIfTheyHappenWithinFiveMsOfEachOther(() => {
        sendRequestToServer()
    })
}

let requestBufferTimeout

function bundleMultipleRequestsTogetherIfTheyHappenWithinFiveMsOfEachOther(callback) {
    if (requestBufferTimeout) return

    requestBufferTimeout = setTimeout(() => {
        callback()

        requestBufferTimeout = undefined
    }, 5)
}

/**
 * This method prepares the network request payload and makes
 * the actual request to the server to update the target,
 * store a new snapshot, and handle any side effects.
 */
async function sendRequestToServer() {
    await queueNewRequestAttemptsWhile(async () => {
        let [payload, handleSuccess, handleFailure] = compileCommitPayloads()

        let options = {
            method: 'POST',
            body: JSON.stringify({
                _token: getCsrfToken(),
                components: payload,
            }),
            headers: {
                'Content-type': 'application/json',
                'X-Livewire': '',
            },
        }

        let succeedCallbacks = []
        let failCallbacks = []
        let respondCallbacks = []

        let succeed = (fwd) => succeedCallbacks.forEach(i => i(fwd))
        let fail = (fwd) => failCallbacks.forEach(i => i(fwd))
        let respond = (fwd) => respondCallbacks.forEach(i => i(fwd))

        let finishProfile = trigger('request.profile', options)

        trigger('request', {
            url: updateUri,
            options,
            payload: options.body,
            respond: i => respondCallbacks.push(i),
            succeed: i => succeedCallbacks.push(i),
            fail: i => failCallbacks.push(i),
        })

        let response = await fetch(updateUri, options)

        let mutableObject = {
            status: response.status,
            response,
        }

        respond(mutableObject)

        response = mutableObject.response

        let content = await response.text()

        // Handle error response...
        if (! response.ok) {
            finishProfile({ content: '{}', failed: true })

            let preventDefault = false

            fail({
                status: response.status,
                content,
                preventDefault: () => preventDefault = true,
            })

            if (preventDefault) return

            if (response.status === 419) {
                handlePageExpiry()
            }

            return showFailureModal(content)
        }

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

            finishProfile({ content: '{}', failed: true })
        } else {
            finishProfile({ content, failed: false })
        }

        let { components } = JSON.parse(content)

        handleSuccess(components)

        succeed({ status: response.status, json: JSON.parse(content) })
    })
}

function compileCommitPayloads() {
    let commits = getCommits()

    // Give each commit a chance to do any last-minute prep
    // before being sent to the server.
    commits.forEach(i => i.prepare())

    let commitPayloads = []

    let successReceivers = []
    let failureReceivers = []

    flushCommits(commit => {
        let [payload, succeed, fail] = commit.toRequestPayload()

        commitPayloads.push(payload)
        successReceivers.push(succeed)
        failureReceivers.push(fail)
    })

    let succeed = components => successReceivers.forEach(receiver => receiver(components.shift()))

    let fail = () => failureReceivers.forEach(receiver => receiver())

    return [ commitPayloads, succeed, fail ]
}

function handlePageExpiry() {
    confirm(
        'This page has expired.\nWould you like to refresh the page?'
    ) && window.location.reload()
}

function showFailureModal(content) {
    let html = content

    showHtmlModal(html)
}

let sendingRequest = false

let afterSendStack = []

export async function waitUntilTheCurrentRequestIsFinished(callback) {
    return new Promise(resolve => {
        if (sendingRequest) {
            afterSendStack.push(() => resolve(callback()))
        } else {
            resolve(callback())
        }
    })
}

async function queueNewRequestAttemptsWhile(callback) {
    sendingRequest = true

    await callback()

    sendingRequest = false

    while (afterSendStack.length > 0) afterSendStack.shift()()
}
