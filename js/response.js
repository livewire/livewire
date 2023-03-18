let invalidResponseHandler

export function hasInvalidResponseHandler() {
    return !! invalidResponseHandler
}

export function onInvalidResponse(closure) {
    invalidResponseHandler = closure
}

export async function handleInvalidResponse(response, defaultHandler = () => {}) {
    let handler = invalidResponseHandler ?? defaultHandler
    await handler(response)
}

export function getInvalidResponseHandler() {
    return invalidResponseHandler
}