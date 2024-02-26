
class Snapshot {
    constructor(url, html) {
        this.url = url
        this.html = html
    }
}

let snapshotCache = {
    lookup: [],

    currentIndex: 0,

    has(idx) {
        return this.lookup[idx] !== undefined
    },

    retrieve(idx) {
        this.currentIndex = idx

        let snapshot = this.lookup[idx]

        if (snapshot === undefined) throw 'No back button cache found for current index: ' + this.currentIndex

        return snapshot
    },

    replace(snapshot) {
        this.lookup[this.currentIndex] = snapshot

        return this.currentIndex
    },

    push(snapshot) {
        // Delete everything forward of this point in time...
        this.lookup.splice(this.currentIndex + 1)

        let idx = this.lookup.push(snapshot) - 1

        this.currentIndex = idx

        return this.currentIndex
    },
}

export function updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks() {
    // Create a history state entry for the initial page load.
    // (This is so later hitting back can restore this page).
    let url = new URL(window.location.href, document.baseURI)

    replaceUrl(url, document.documentElement.outerHTML)
}

export function whenTheBackOrForwardButtonIsClicked(registerFallback, handleHtml) {
    console.log('whenTheBackOrForwardButtonIsClicked')
    let fallback

    registerFallback(i => fallback = i)

    window.addEventListener('popstate', e => {
        console.log('popstate')
        let state = e.state || {}

        let alpine = state.alpine || {}

        console.log('id', alpine.snapshotIdx)
        console.log('snapshotCache', snapshotCache)

        if (snapshotCache.has(alpine.snapshotIdx)) {
            let snapshot = snapshotCache.retrieve(alpine.snapshotIdx)

            console.log('handleHtml', snapshot)
            handleHtml(snapshot.html)
        } else {
            console.log('fallback')
            fallback(alpine.url)
        }
    })
}

export function updateUrlAndStoreLatestHtmlForFutureBackButtons(html, destination) {
    pushUrl(destination, html)
}

export function pushUrl(url, html) {
    updateUrl('pushState', url, html)
}

export function replaceUrl(url, html) {
    updateUrl('replaceState', url, html)
}

function updateUrl(method, url, html) {
    console.log('updateUrl', method, url, html)
    let key = method === 'pushState'
        ? snapshotCache.push(new Snapshot(url, html))
        : snapshotCache.replace(new Snapshot(url, html))

    console.log('snapshotCache', snapshotCache)
    let state = history.state || {}

    if (! state.alpine) state.alpine = {}

    state.alpine.url = url.toString()
    state.alpine.snapshotIdx = key

    try {
        // 640k character limit:
        history[method](state, document.title, url)
    } catch (error) {
        if (error instanceof DOMException && error.name === 'SecurityError') {
            console.error('Livewire: You can\'t use wire:navigate with a link to a different root domain: '+url)
        }

        console.error(error)
    }
}

