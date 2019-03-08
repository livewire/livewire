
export default class {
    constructor(component) {
        this.component = component
        this.id = Math.random().toString(36).substring(7)
    }

    prepareForSend() {
        this.loadingEls = this.component.setLoading(this.ref)
    }

    payload() {
        // This sends over lazilly updated wire:model attributes.
        const syncQueue = this.component.syncQueue

        this.component.clearSyncQueue()

        return {
            type: this.type,
            serialized: this.component.serialized,
            data: {
                ...this.payloadPortion,
                ...{
                    syncQueue: syncQueue,
                    componentId: this.component.id,
                },
            }
        }
    }

    storeResponse(payload) {
        const { dom, dirtyInputs, serialized, redirectTo, emitEvent } = payload

        this.response = {
            dom: payload.dom,
            dirtyInputs: payload.dirtyInputs,
            serialized: payload.serialized,
            redirectTo: redirectTo,
            emitEvent: emitEvent,
        }
    }
}
