
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

    actionHasPrefetch(action) {
        return Object.keys(this.prefetchMessagesByActionId).includes(action.toId())
    }

    actionPrefetchResponseHasBeenReceived(action) {
        return !! this.getPrefetchMessageByAction(action).response
    }

    getPrefetchMessageByAction(action) {
        return this.prefetchMessagesByActionId[action.toId()]
    }

    clearPrefetches() {
        this.prefetchMessagesByActionId = {}
    }
}

export default PrefetchManager
