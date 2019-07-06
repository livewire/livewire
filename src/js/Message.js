
export default class {
    constructor(component, actionQueue) {
        this.component = component
        this.actionQueue = actionQueue
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
            name: this.component.name,
            children: this.component.children,
            middleware: this.component.middleware,
            checksum: this.component.checksum,
            actionQueue: this.actionQueue.map(action => {
                // This ensures only the type & payload properties only get sent over.
                return {
                    type: action.type,
                    payload: action.payload,
                }
            }),
        }
    }

    storeResponse(payload) {
        return this.response = {
            id: payload.id,
            dom: payload.dom,
            children: payload.children,
            dirtyInputs: payload.dirtyInputs,
            eventQueue: payload.eventQueue,
            events: payload.events,
            data: payload.data,
            redirectTo: payload.redirectTo,
        }
    }
}
