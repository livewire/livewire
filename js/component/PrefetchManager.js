class PrefetchManager {
    constructor(component) {
        this.component = component
        this.prefetchMessagesByActionId = {}
    }

    addMessage(message) {
        this.prefetchMessagesByActionId[message.prefetchId] = message
    }

    actionHasPrefetch(action) {
        return Object.keys(this.prefetchMessagesByActionId).includes(
            action.toId()
        )
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
