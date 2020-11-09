export default class {
    constructor(component, updateQueue) {
        this.component = component
        this.updateQueue = updateQueue
    }

    payload() {
        return {
            fingerprint: this.component.fingerprint,
            serverMemo: this.component.serverMemo,
            // This ensures only the type & payload properties only get sent over.
            updates: this.updateQueue.map(update => ({
                type: update.type,
                payload: update.payload,
            })),
        }
    }

    shouldSkipWatcherForDataKey(dataKey) {
        // If the data is dirty, run the watcher.
        if (this.response.effects.dirty.includes(dataKey)) return false

        let compareBeforeFirstDot = (subject, value) => {
            if (typeof subject !== 'string' || typeof value !== 'string') return false

            return subject.split('.')[0] === value.split('.')[0]
        }

        // Otherwise see if there was a defered update for a data key.
        // In that case, we want to skip running the Livewire watcher.
        return this.updateQueue
            .filter(update => compareBeforeFirstDot(update.name, dataKey))
            .some(update => update.skipWatcher)
    }

    storeResponse(payload) {
        return (this.response = payload)
    }

    resolve() {
        let returns = this.response.effects.returns || []

        this.updateQueue.forEach(update => {
            if (update.type !== 'callMethod') return

            update.resolve(
                returns[update.method] !== undefined
                    ? returns[update.method]
                    : null
            )
        })
    }

    reject() {
        this.updateQueue.forEach(update => {
            update.reject()
        })
    }
}
