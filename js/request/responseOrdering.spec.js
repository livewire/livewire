import { describe, expect, it } from 'vitest'
import { shouldDiscardLiveModelResponse, trackLiveModelRequest } from './responseOrdering.js'

describe('Live model response ordering', () => {
    it('keeps response ordering isolated by request scope', () => {
        let component = {}
        let componentScope = Symbol()
        let islandScope = Symbol()
        let firstComponentRequest = createMessage(component, componentScope)
        let secondComponentRequest = createMessage(component, componentScope)
        let firstIslandRequest = createMessage(component, islandScope)

        trackLiveModelRequest(firstComponentRequest)
        trackLiveModelRequest(secondComponentRequest)
        trackLiveModelRequest(firstIslandRequest)

        expect(shouldDiscardLiveModelResponse(secondComponentRequest)).toBe(false)
        expect(shouldDiscardLiveModelResponse(firstIslandRequest)).toBe(false)
        expect(shouldDiscardLiveModelResponse(firstComponentRequest)).toBe(true)
    })

    it('does not order messages containing non-live-model actions', () => {
        let message = createMessage({}, Symbol(), ['model.live', 'click'])

        trackLiveModelRequest(message)

        expect(message.liveModelRequestOrder).toBeUndefined()
        expect(shouldDiscardLiveModelResponse(message)).toBe(false)
    })
})

function createMessage(component, scope, actionTypes = ['model.live']) {
    return {
        component,
        scope,
        actions: new Set(actionTypes.map(type => ({ metadata: { type } }))),
    }
}
