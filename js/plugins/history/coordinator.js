import { unwrap } from "./utils"

class HistoryCoordinator {
    constructor() {
        this.pendingUpdates = {}
        this.pendingUrl = null
        this.pendingTimeout = null
        this.errorHandlers = {}
    }

    getUrl() {
        return this.pendingUrl ?? new URL(window.location.href)
    }

    flushReplaces() {
        if (this.pendingTimeout) {
            clearTimeout(this.pendingTimeout)
            this.pendingTimeout = null
        }

        if (Object.keys(this.pendingUpdates).length === 0 || !this.pendingUrl) {
            return
        }

        let state = window.history.state || {}
        if (!state.alpine) state.alpine = {}

        state.alpine = {
            ...state.alpine,
            ...this.pendingUpdates
        }

        let url = this.pendingUrl

        this.pendingUpdates = {}
        this.pendingUrl = null

        try {
            // 640k character limit:
            console.log('actually replacing state', state, url.toString())
            window.history.replaceState(state, '', url.toString())
        } catch (error) {
            let errorHandlers = this.errorHandlers
            this.errorHandlers = {}

            Object.values(errorHandlers).forEach(
                handler => typeof handler === 'function' && handler(error, url)
            )

            console.error(error)
        }
    }


    replaceState(url, updates, errorHandlers = {}) {
        this.errorHandlers = {...this.errorHandlers, ...errorHandlers}

        Object.assign(this.pendingUpdates, unwrap(updates))

        this.pendingUrl = url

        if (!this.pendingTimeout) {
            this.pendingTimeout = setTimeout(() => {
                this.pendingTimeout = null
                this.flushReplaces()
            }, 0)
        }
    }

    pushState(url, updates, errorHandlers = {}) {
        this.errorHandlers = {...this.errorHandlers, ...errorHandlers}

        if (this.pendingTimeout) {
            clearTimeout(this.pendingTimeout)
            this.pendingTimeout = null
        }

        // Flush any pending replaces first
        if (Object.keys(this.pendingUpdates).length > 0) {
            this.flushReplaces()
        }

        let state = window.history.state || {}
        if (!state.alpine) state.alpine = {}

        state = { alpine: { ...state.alpine, ...unwrap(updates) } }

        try {
            // 640k character limit:
            console.log('actually pushing state', state, url.toString())
            window.history.pushState(state, '', url.toString())
        } catch (error) {
            let errorHandlers = this.errorHandlers
            this.errorHandlers = {}

            Object.values(errorHandlers).forEach(
                handler => typeof handler === 'function' && handler(error, url)
            )

            console.error(error)
        }
    }
}

let historyCoordinator = new HistoryCoordinator()

export default historyCoordinator
