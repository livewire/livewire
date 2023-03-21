import { showHtmlModal } from "./modal"

let errorResponseHandler

export function onErrorResponse(handler) {
    errorResponseHandler = handler
}

export async function handleResponse(response, success, fail) {
    let content = await response.text()

    if (response.ok && ! response.redirected) {
        if (contentIsFromDump(content)) {
            content = removeLivewireContentFromDump(content)
        } else {
            return await success(content)
        }
    }

    let shouldContinue = true

    if (errorResponseHandler) {
        errorResponseHandler(response, content, () => shouldContinue = false)

        if (! shouldContinue) return await fail()
    }

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

function removeLivewireContentFromDump(content) {
    return content.match(/.*<script>Sfdump\(".+"\)<\/script>/s)
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
