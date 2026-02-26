import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushOfflineActionQueue, getOfflineActionQueueSize, queueActionForOffline, resetOfflineActionQueue } from './offlineQueue'

function createAction({ cancelled = false } = {}) {
    return {
        defer: vi.fn(),
        fire: vi.fn(),
        isCancelled: () => cancelled,
    }
}

describe('Offline action queue', () => {
    beforeEach(() => {
        resetOfflineActionQueue()
    })

    it('queues actions and marks them deferred', () => {
        let action = createAction()

        queueActionForOffline(action)

        expect(action.defer).toHaveBeenCalledOnce()
        expect(getOfflineActionQueueSize()).toBe(1)
    })

    it('flushes queued actions when back online', () => {
        let action = createAction()

        queueActionForOffline(action)
        flushOfflineActionQueue()

        expect(action.fire).toHaveBeenCalledOnce()
        expect(getOfflineActionQueueSize()).toBe(0)
    })

    it('skips cancelled actions while flushing', () => {
        let cancelledAction = createAction({ cancelled: true })

        queueActionForOffline(cancelledAction)
        flushOfflineActionQueue()

        expect(cancelledAction.fire).not.toHaveBeenCalled()
    })
})
