
let backButtonCache = {
    lookup: [],

    currentIndex: 0,

    retrieve(idx) {
        this.currentIndex = idx

        let html = this.lookup[idx]

        if (html === undefined) throw 'No back button cache found for current index: ' + this.currentIndex

        return html
    },

    replace(html) {
        this.lookup[this.currentIndex] = html

        return this.currentIndex
    },

    push(html) {
        // Delete everything forward of this point in time...
        this.lookup.splice(this.currentIndex + 1)

        let idx = this.lookup.push(html) - 1

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

export function whenTheBackOrForwardButtonIsClicked(callback) {
    window.addEventListener('popstate', e => {
        let state = e.state || {}

        let alpine = state.alpine || {}

        if (alpine._html === undefined) return

        let html = backButtonCache.retrieve(alpine._html)
        // let html = fromSessionStorage(alpine._html)

        callback(html)
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
    let key = method === 'pushState'
        ? backButtonCache.push(html)
        : backButtonCache.replace(html)

    let state = history.state || {}

    if (! state.alpine) state.alpine = {}

    state.alpine._html = key

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

