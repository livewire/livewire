import store from '@/Store'

export default class {
    constructor(component, actionQueue) {
        this.component = component
        this.actionQueue = actionQueue
    }

    get refs() {
        return this.actionQueue
            .map(action => {
                return action.ref
            })
            .filter(ref => ref)
    }

    payload() {
        let payload = {
            fingerprint: this.component.fingerprint,
            memo: this.component.memo,
            updates: this.actionQueue.map(update => {
                // This ensures only the type & payload properties only get sent over.
                return {
                    type: update.type,
                    payload: update.payload,
                }
            }),
        }

        return payload
    }

    storeResponse(payload) {
        return this.response = {
            memo: payload.memo,
            effects: payload.effects,
        }
    }
}
