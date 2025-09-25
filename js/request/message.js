
export default class Message {
    actions = []
    promiseResolversByAction = new Map()
    snapshot = null
    updates = null
    calls = null
    payload = null
    responsePayload = null
    respondCallbacks = []
    succeedCallbacks = []
    failCallbacks = []

    constructor(component) {
        this.component = component
    }

    addAction(action, promiseResolver) {
        this.actions.push(action)
        this.promiseResolversByAction.set(action, promiseResolver)
    }

    respond() {
        this.respondCallbacks.forEach(i => i())
    }

    fail() {
        this.failCallbacks.forEach(i => i())

        // Reject any promises...
        this.actions.forEach(action => {
            let promiseResolver = this.promiseResolversByAction.get(action)

            if (! promiseResolver) return;

            promiseResolver.reject()
        })
    }

    succeed() {
        this.succeedCallbacks.forEach(i => i(this.responsePayload))

        // Process any returned values...
        let returns = this.responsePayload.effects['returns']

        if (! returns) return;

        returns.forEach((value, index) => {
            let action = this.actions[index]

            if (! action) return;

            let promiseResolver = this.promiseResolversByAction.get(action)

            if (! promiseResolver) return;

            promiseResolver.resolve(value)
        })
    }
}
