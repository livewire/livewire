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

    shouldSkipWatcher() {
        return this.updateQueue.length && this.updateQueue.every(update => update.skipWatcher)
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
