import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri } from '@/utils'
import { trigger, triggerAsync } from '@/hooks'
import { showHtmlModal } from './modal'
import { CommitBus } from './bus'

/**
 * This is the bus that manages pooling and sending
 * commits to the server as network requests...
 */
let commitBus = new CommitBus

/**
 * Create a commit and trigger a network request...
 */
export async function requestCommit(component) {
    let commit = commitBus.add(component)

    let promise = new Promise((resolve) => {
        commit.addResolver(resolve)
    })

    promise.commit = commit

    return promise
}

/**
 * Create a commit with an "action" call and trigger a network request...
 */
export async function requestCall(component, method, params) {
    let commit = commitBus.add(component)

    let promise = new Promise((resolve) => {
        commit.addCall(method, params, value => resolve(value))
    })

    promise.commit = commit

    return promise
}

/**
 * Send a pool of commits to the server over HTTP...
 */
export async function sendRequest(pool) {
    let [payload, handleSuccess, handleFailure] = pool.payload()

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

    let updateUri = getUpdateUri()

    trigger('request', {
        url: updateUri,
        options,
        payload: options.body,
        respond: i => respondCallbacks.push(i),
        succeed: i => succeedCallbacks.push(i),
        fail: i => failCallbacks.push(i),
    })

    let response

    try {
        response = await fetch(updateUri, options)
    } catch (e) {
        // If something went wrong with the fetch (particularly
        // this would happen if the connection went offline)
        // fail with a 503 and allow Livewire to clean up

        finishProfile({ content: '{}', failed: true })

        handleFailure()

        fail({
            status: 503,
            content: null,
            preventDefault: () => {},
        })

        return
    }

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

        handleFailure()

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
        let dump
        [dump, content] = splitDumpFromContent(content)

        showHtmlModal(dump)

        finishProfile({ content: '{}', failed: true })
    } else {
        finishProfile({ content, failed: false })
    }

    let { components, assets } = JSON.parse(content)

    await triggerAsync('payload.intercept', { components, assets })

    await handleSuccess(components)

    succeed({ status: response.status, json: JSON.parse(content) })
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
