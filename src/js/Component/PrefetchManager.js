
class PrefetchManager {
    constructor(component) {
        this.component = component
        this.prefetchMessagesByActionId = {}
    }

    addMessage(message) {
        this.prefetchMessagesByActionId[message.prefetchId] = message
    }

    storeResponseInMessageForPayload(payload) {
        const message = this.prefetchMessagesByActionId[payload.fromPrefetch]

        if (message) message.storeResponse(payload)
    }

    actionHasPrefetch({ toId }) {
        return Object.keys(this.prefetchMessagesByActionId).includes(toId())
    }

    actionPrefetchResponseHasBeenReceived(action) {
        return !! this.getPrefetchMessageByAction(action).response
    }

    getPrefetchMessageByAction({ toId }) {
        return this.prefetchMessagesByActionId[toId()]
    }

    clearPrefetches() {
        this.prefetchMessagesByActionId = {}
    }
}

export default PrefetchManager
