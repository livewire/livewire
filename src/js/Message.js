
export default class {
    constructor(component, actionQueue, syncQueue) {
        this.component = component
        this.actionQueue = actionQueue
        this.syncQueue = syncQueue
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
            id: this.component.id,
            data: this.component.data,
            class: this.component.componentClass,
            syncQueue: this.syncQueue,
            actionQueue: this.actionQueue,
        }
    }

    storeResponse(payload) {
        this.response = {
            id: payload.id,
            dom: payload.dom,
            dirtyInputs: payload.dirtyInputs,
            eventQueue: payload.eventQueue,
            listeningFor: payload.listeningFor,
            data: payload.data,
            redirectTo: payload.redirectTo,
        }
    }
}
