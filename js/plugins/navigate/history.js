
class Snapshot {
    constructor(url, html) {
        this.url = url
        this.html = html
    }
}

let snapshotCache = {
    currentKey: null,
    currentUrl: null,
    keys: [],
    lookup: {},

    limit: 10,

    has(location) {
        return this.lookup[location] !== undefined
    },

    retrieve(location) {
        let snapshot = this.lookup[location]

        if (snapshot === undefined)
            throw (
                'No back button cache found for current location: ' +
                location
            )

        return snapshot
    },

    replace(key, snapshot) {
        if (this.has(key)) {
            this.lookup[key] = snapshot
        } else {
            this.push(key, snapshot)
        }
    },

    push(key, snapshot) {
        this.lookup[key] = snapshot

        let index = this.keys.indexOf(key)

        if (index > -1) this.keys.splice(index, 1)

        this.keys.unshift(key)

        this.trim()
    },

    trim() {
        for (let key of this.keys.splice(this.limit)) {
          delete this.lookup[key]
        }
    }
}

export function updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks() {
    // Create a history state entry for the initial page load.
    // (This is so later hitting back can restore this page).
    let url = new URL(window.location.href, document.baseURI)

    replaceUrl(url, document.documentElement.outerHTML)
}

export function updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks(key, url) {
    let html = document.documentElement.outerHTML

    snapshotCache.replace(key, new Snapshot(url, html))
}

export function whenTheBackOrForwardButtonIsClicked(
    registerFallback,
    handleHtml
) {
    let fallback

    registerFallback(i => (fallback = i))

    window.addEventListener('popstate', e => {
        let state = e.state || {}

        let alpine = state.alpine || {}

        // If state is an empty object, then the popstate has probably been triggered
        // by anchor tags `#my-heading`, so we don't want to handle them.
        if (Object.keys(state).length === 0) return

        if (! alpine.snapshotIdx) return

        if (snapshotCache.has(alpine.snapshotIdx)) {
            let snapshot = snapshotCache.retrieve(alpine.snapshotIdx)

            handleHtml(snapshot.html, snapshot.url, snapshotCache.currentUrl, snapshotCache.currentKey)
        } else {
            fallback(alpine.url)
        }
    })
}

export function updateUrlAndStoreLatestHtmlForFutureBackButtons(
    html,
    destination
) {
    pushUrl(destination, html)
}

export function pushUrl(url, html) {
    updateUrl('pushState', url, html)
}

export function replaceUrl(url, html) {
    updateUrl('replaceState', url, html)
}

function updateUrl(method, url, html) {
    let key = url.toString() + '-' + Math.random()

    method === 'pushState'
        ? snapshotCache.push(key, new Snapshot(url, html))
        : snapshotCache.replace(key = (snapshotCache.currentKey ?? key), new Snapshot(url, html))

    let state = history.state || {}

    if (!state.alpine) state.alpine = {}

    state.alpine.snapshotIdx = key
    state.alpine.url = url.toString()

    try {
        // 640k character limit:
        history[method](state, JSON.stringify(document.title), url)

        snapshotCache.currentKey = key
        snapshotCache.currentUrl = url
    } catch (error) {
        if (error instanceof DOMException && error.name === 'SecurityError') {
            console.error(
                "Livewire: You can't use wire:navigate with a link to a different root domain: " +
                    url
            )
        }

        console.error(error)
    }
}
