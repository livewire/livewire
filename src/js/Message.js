
export default class {
    constructor(component, actionQueue) {
        this.component = component
        this.actionQueue = actionQueue
    }

    get refs() {
        return this.actionQueue
            .map(({ ref }) => ref)
            .filter(ref => ref)
    }

    payload() {
        const { id, data, name, children, middleware, checksum } = this.component
        return {
            id,
            data,
            name,
            children,
            middleware,
            checksum,
            actionQueue: this.actionQueue.map(action => {
                // This ensures only the type & payload properties only get sent over.
                return {
                    type: action.type,
                    payload: action.payload,
                }
            }),
        }
    }

    storeResponse({ id, dom, children, dirtyInputs, eventQueue, events, data, redirectTo }) {
        return this.response = {
            id,
            dom,
            children,
            dirtyInputs,
            eventQueue,
            events,
            data,
            redirectTo,
        }
    }
}
