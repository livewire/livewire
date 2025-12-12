import { trigger } from '@/hooks.js'

export function registerLegacyEventSupport(interceptRequest, interceptMessage) {

// Support legacy 'request' event...
interceptRequest(({
    request,
    onFailure,
    onResponse,
    onError,
    onSuccess,
}) => {
    let respondCallbacks = []
    let succeedCallbacks = []
    let failCallbacks = []

    trigger('request', {
        url: request.uri,
        options: request.options,
        payload: request.options.body,
        respond: i => respondCallbacks.push(i),
        succeed: i => succeedCallbacks.push(i),
        fail: i => failCallbacks.push(i),
    })

    onResponse(({ response }) => {
        respondCallbacks.forEach(callback => callback({
            status: response.status,
            response,
        }))
    })

    onSuccess(({ response, json }) => {
        succeedCallbacks.forEach(callback => callback({
            status: response.status,
            json,
        }))
    })

    onFailure(({ error }) => {
        failCallbacks.forEach(callback => callback({
            status: 503,
            content: null,
            preventDefault: () => {},
        }))
    })

    onError(({ response, body, preventDefault }) => {
        failCallbacks.forEach(callback => callback({
            status: response.status,
            content: body,
            preventDefault,
        }))
    })
})

// Support legacy 'commit' event...
interceptMessage(({
    message,
    onCancel,
    onError,
    onSuccess,
    onFinish,
}) => {
    // Allow other areas of the codebase to hook into the lifecycle
    // of an individual commit...
    let respondCallbacks = []
    let succeedCallbacks = []
    let failCallbacks = []

    trigger('commit', {
        component: message.component,
        commit: message.payload,
        respond: (callback) => {
            respondCallbacks.push(callback)
        },
        succeed: (callback) => {
            succeedCallbacks.push(callback)
        },
        fail: (callback) => {
            failCallbacks.push(callback)
        },
    })

    onFinish(() => {
        respondCallbacks.forEach(callback => callback())
    })

    onSuccess(({ payload, onSync, onMorph, onRender }) => {
        onRender(() => {
            succeedCallbacks.forEach(callback => callback({
                snapshot: payload.snapshot,
                effects: payload.effects,
            }))
        })
    })

    onError(() => {
        failCallbacks.forEach(callback => callback())
    })

    onCancel(() => {
        failCallbacks.forEach(callback => callback())
    })
})

}
