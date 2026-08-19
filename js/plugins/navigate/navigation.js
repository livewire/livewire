let activeNavigation
let navigationStartListeners = new Set

export function startNavigation() {
    activeNavigation?.cancel()

    let navigation = new Navigation(() => {
        if (activeNavigation === navigation) activeNavigation = undefined
    })

    activeNavigation = navigation

    navigationStartListeners.forEach(callback => callback(navigation))

    return navigation
}

export function onNavigationStart(callback) {
    navigationStartListeners.add(callback)

    return () => navigationStartListeners.delete(callback)
}

export function getActiveNavigation() {
    return activeNavigation
}

export function isNavigating() {
    return activeNavigation !== undefined
}

class Navigation {
    constructor(onComplete) {
        this.state = 'fetching'
        this.onComplete = onComplete
        this.listeners = {
            ready: new Set,
            cancelled: new Set,
            finished: new Set,
        }
    }

    onReady(callback) {
        if (this.state === 'ready' || this.state === 'finished') {
            callback()

            return () => {}
        }

        return this.listen('ready', callback)
    }

    onCancelled(callback) {
        if (this.state === 'cancelled') {
            callback()

            return () => {}
        }

        return this.listen('cancelled', callback)
    }

    onFinished(callback) {
        if (this.state === 'finished') {
            callback()

            return () => {}
        }

        return this.listen('finished', callback)
    }

    ready() {
        if (this.state !== 'fetching') return

        this.state = 'ready'

        this.emit('ready')
    }

    cancel() {
        if (this.state === 'cancelled' || this.state === 'finished') return

        this.state = 'cancelled'

        this.emit('cancelled')
        this.complete()
    }

    finish() {
        if (this.state === 'cancelled' || this.state === 'finished') return

        this.state = 'finished'

        this.emit('finished')
        this.complete()
    }

    listen(event, callback) {
        this.listeners[event].add(callback)

        return () => this.listeners[event].delete(callback)
    }

    emit(event) {
        this.listeners[event].forEach(callback => callback())
        this.listeners[event].clear()
    }

    complete() {
        Object.values(this.listeners).forEach(listeners => listeners.clear())

        this.onComplete()
    }
}
