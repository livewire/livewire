import { getCsrfToken, contentIsFromDump, splitDumpFromContent, getUpdateUri } from '@/utils'
import { showHtmlModal } from './modal'
import { trigger, triggerAsync } from '@/events'

/**
 * This method prepares the network request payload and makes
 * the actual request to the server to update the target,
 * store a new snapshot, and handle any side effects.
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

