import { batch, unwrap } from "./utils"

class HistoryCoordinator {
    constructor() {
        this.url = null
        this.errorHandlers = {}

        this.batch = batch((updates) => {
            let url = this.getUrl()

            this.writeToHistory('replaceState', url, (state) => {
                // Only update state.alpine as we are merging...
                state.alpine = { ...state.alpine, ...unwrap(updates) }

                return state
            })

            this.url = null
        })
    }

    addErrorHandler(key, callback) {
        this.errorHandlers[key] = callback
    }

    getUrl() {
        // If the querystring has started changing the URL before the batch has been flushed, use the URL that was passed in...
        if (this.url) {
            // Always pick up the current hash from the browser, since Livewire only manages query strings
            // and the hash may have been changed externally (e.g. during Alpine init)...
            this.url.hash = window.location.hash
            return this.url
        }

        return new URL(window.location.href)
    }

    replaceState(url, updates) {
        this.url = url
        this.batch.add(updates)
    }

    pushState(url, updates) {
        // Flush any pending replaces first...
        this.batch.flush()

        this.writeToHistory('pushState', url, (state) => {
            // Replace the entire state as we are pushing...
            state = { alpine: { ...state.alpine, ...unwrap(updates) } }

            return state
        })
    }

    writeToHistory(method, url, callback) {
        let state = window.history.state || {}
        if (!state.alpine) state.alpine = {}

        // Process the state using the callback...
        state = callback(state)

        try {
            // 640k character limit:
            window.history[method](state, '', url.toString())
        } catch (error) {
            Object.values(this.errorHandlers).forEach(
                handler => typeof handler === 'function' && handler(error, url)
            )

            // Remove error handlers after processing as they could be different depending on what is calling the method...
            this.errorHandlers = {}

            console.error(error)
        }
    }
}

let historyCoordinator = new HistoryCoordinator()

export default historyCoordinator
