let offlineActionQueue = []
let hasRegisteredOfflineListener = false

export function queueActionForOffline(action) {
    registerOfflineListener()

    action.defer()

    offlineActionQueue.push(action)
}

export function flushOfflineActionQueue() {
    let queued = offlineActionQueue

    offlineActionQueue = []

    queued.forEach(action => {
        if (action.isCancelled()) return

        action.fire()
    })
}

export function getOfflineActionQueueSize() {
    return offlineActionQueue.length
}

export function resetOfflineActionQueue() {
    offlineActionQueue = []
}

function registerOfflineListener() {
    if (hasRegisteredOfflineListener) return

    hasRegisteredOfflineListener = true

    window.addEventListener('online', () => {
        flushOfflineActionQueue()
    })
}
