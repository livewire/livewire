
export default class {
    constructor(component, actionQueue, syncQueue) {
        this.component = component
        this.actionQueue = actionQueue
        this.syncQueue = syncQueue

        this.id = Math.random().toString(36).substring(7)
    }

    prepareForSend() {
        this.loadingEls = this.component.setLoading(this.refs)
    }

    get refs() {
        return this.actionQueue
            .map(action => {
                return action.ref
            })
            .filter(ref => ref)
    }

    payload() {
        return {
            serialized: this.component.serialized,
            componentId: this.component.id,
            syncQueue: this.syncQueue,
            actionQueue: this.actionQueue,
        }
    }

    storeResponse(payload) {
        this.response = {
            dom: payload.dom,
            dirtyInputs: payload.dirtyInputs,
            serialized: payload.serialized,
            redirectTo: payload.redirectTo,
            emitEvent: payload.emitEvent,
            forQueryString: payload.forQueryString,
        }
    }
}
