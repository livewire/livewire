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

    storeResponse(payload) {
        super.storeResponse(payload)

        this.response.fromPrefetch = payload.fromPrefetch

        return this.response
    }
}
