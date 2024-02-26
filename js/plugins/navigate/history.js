class Snapshot {
    constructor(url, html) {
        this.url = url
        this.html = html
    }
}

let snapshotCache = {
    keys: [],
    lookup: {},

    limit: 10,

    toKey(location) {
        return location.toString()
    },

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

    replace(location, snapshot) {
        if (this.has(location)) {
            this.lookup[location] = snapshot
        } else {
            this.push(location, snapshot)
        }
    },

    push(location, snapshot) {
        this.lookup[location] = snapshot

        let key = this.toKey(location)
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

        if (snapshotCache.has(alpine.url)) {
            let snapshot = snapshotCache.retrieve(alpine.url)

            handleHtml(snapshot.html)
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
    let key =
        method === 'pushState'
            ? snapshotCache.push(url, new Snapshot(url, html))
            : snapshotCache.replace(url, new Snapshot(url, html))

    let state = history.state || {}

    if (!state.alpine) state.alpine = {}

    state.alpine.url = url.toString()

    try {
        // 640k character limit:
        history[method](state, JSON.stringify(document.title), url)
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
