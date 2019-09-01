import Message from '@/Message'

export default class extends Message {
    constructor(component, action) {
        super(component, [action])
    }

    get prefetchId() {
        return this.actionQueue[0].toId()
    }

    payload() {
        return {
            fromPrefetch: this.prefetchId,
            ...super.payload()
        }
    }

    garbageCollectionIds() {
        // Bypass garbage collecting on prefetches,
        // because they haven't "happened" yet,
        // and we don't want to clear other
        // component's sessions prematurely.
        return []
    }

    storeResponse(payload) {
        super.storeResponse(payload)

        this.response.fromPrefetch = payload.fromPrefetch

        return this.response
    }
}
