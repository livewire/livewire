import store from '@/Store'

export default class {
    constructor(component, updates) {
        this.component = component
        this.updates = updates
    }

    get refs() {
        return this.updates
            .map(action => {
                return action.ref
            })
            .filter(ref => ref)
    }

    payload() {
        let payload = {
            fingerprint: this.component.fingerprint,
            memo: this.component.memo,
            updates: this.updates.map(update => {
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

    resolve() {
        let returns = this.response.effects.returns || []

        this.updates.forEach(update => {
            if (update.type !== 'callMethod') return

            update.resolve(returns[update.method] !== undefined ? returns[update.method] : null)
        })
    }

    reject() {
        this.updates.forEach(update => {
            update.reject()
        })
    }
}
