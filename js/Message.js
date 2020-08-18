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

    storeResponse(payload) {
        return (this.response = payload)
    }
}
