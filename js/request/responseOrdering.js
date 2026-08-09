let liveModelResponseOrders = new WeakMap

export function trackLiveModelRequest(message) {
    if (! hasOnlyLiveModelActions(message)) return

    let ordersByScope = liveModelResponseOrders.get(message.component)

    if (! ordersByScope) {
        ordersByScope = new Map

        liveModelResponseOrders.set(message.component, ordersByScope)
    }

    let order = ordersByScope.get(message.scope)

    if (! order) {
        order = { latestSent: 0, latestReceived: 0 }

        ordersByScope.set(message.scope, order)
    }

    message.liveModelRequestOrder = ++order.latestSent
}

export function shouldDiscardLiveModelResponse(message) {
    if (message.liveModelRequestOrder === undefined) return false

    let order = liveModelResponseOrders.get(message.component)?.get(message.scope)

    if (! order) return false

    // A response only becomes stale after a newer response has actually arrived.
    // Merely sending a newer request should not prevent this one from succeeding
    // if the newer request eventually fails.
    if (message.liveModelRequestOrder < order.latestReceived) return true

    order.latestReceived = message.liveModelRequestOrder

    return false
}

function hasOnlyLiveModelActions(message) {
    return message.actions.size > 0
        && Array.from(message.actions).every(action => action.metadata.type === 'model.live')
}
