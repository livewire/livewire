import { describe, it, expect, beforeEach } from 'vitest'
import {
    pushUrl,
    updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks,
    whenTheBackOrForwardButtonIsClicked,
} from './history'

function setPageHtml(bodyHtml) {
    document.documentElement.innerHTML = `<head></head><body>${bodyHtml}</body>`
}

function countRenderedButtonsInHtml(html) {
    let doc = (new DOMParser()).parseFromString(html, 'text/html')
    let container = doc.getElementById('alpine-if-container')

    if (! container) return 0

    let count = 0

    container.querySelectorAll('template[x-if]').forEach(template => {
        let sibling = template.nextElementSibling

        while (sibling) {
            if (sibling.matches('button')) count++

            sibling = sibling.nextElementSibling
        }
    })

    return count
}

// Mirrors Alpine.destroyTree() behavior for x-if: rendered nodes live as siblings after the template.
function cleanupAlpineXIfOutput(root = document.body) {
    root.querySelectorAll('template[x-if]').forEach(template => {
        let sibling = template.nextElementSibling

        while (sibling) {
            let next = sibling.nextElementSibling

            sibling.remove()

            sibling = next
        }
    })
}

function pageUrl(path) {
    return new URL(path, window.location.href)
}

async function flushHistoryCoordinator() {
    await new Promise(queueMicrotask)
}

describe('navigate history snapshots', () => {
    beforeEach(() => {
        window.history.replaceState({}, '', '/')

        setPageHtml('<div>reset</div>')
    })

    it('does not cache alpine x-if rendered output when cleanup runs before updating a snapshot', async () => {
        setPageHtml(`
            <div id="alpine-if-container">
                <template x-if="true"><button>Add passkey</button></template>
            </div>
        `)

        pushUrl(pageUrl('/page-a'), document.documentElement.outerHTML)

        await flushHistoryCoordinator()

        let pageAKey = window.history.state.alpine.snapshotIdx

        setPageHtml('<div>page-b</div>')

        pushUrl(pageUrl('/page-b'), document.documentElement.outerHTML)

        await flushHistoryCoordinator()

        // Alpine has re-initialized x-if on page-a, leaving rendered output in the live DOM.
        setPageHtml(`
            <div id="alpine-if-container">
                <template x-if="true"><button>Add passkey</button></template>
                <button>Add passkey</button>
            </div>
        `)

        cleanupAlpineXIfOutput()

        updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks(pageAKey, pageUrl('/page-a'))

        let retrievedHtml = null

        whenTheBackOrForwardButtonIsClicked(() => {}, html => {
            retrievedHtml = html
        })

        window.history.back()

        await flushHistoryCoordinator()

        expect(countRenderedButtonsInHtml(retrievedHtml)).toBe(0)
    })
})
