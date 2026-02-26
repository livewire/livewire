import { describe, it, expect, vi } from 'vitest'
import Message from './message'

function createAction({ optimistic = false, fingerprint = 'action' } = {}) {
    return {
        fingerprint,
        message: null,
        addSquashedAction: () => {},
        isOptimistic: () => optimistic,
        invokeOnError: () => {},
        invokeOnFailure: () => {},
        invokeOnFinish: () => {},
        rejectPromise: () => {},
    }
}

describe('Optimistic rollback', () => {
    it('rolls back optimistic updates when a message errors', () => {
        let rollbackOptimisticUpdates = vi.fn()
        let message = new Message({ rollbackOptimisticUpdates })

        message.optimisticRollback = {
            count: { exists: true, value: 1 },
        }

        message.addAction(createAction({ optimistic: true }))

        message.invokeOnError({
            response: { status: 500 },
            body: '',
            preventDefault: () => {},
        })

        expect(rollbackOptimisticUpdates).toHaveBeenCalledOnce()
        expect(rollbackOptimisticUpdates).toHaveBeenCalledWith({
            count: { exists: true, value: 1 },
        })
    })

    it('does not roll back when no optimistic action is present', () => {
        let rollbackOptimisticUpdates = vi.fn()
        let message = new Message({ rollbackOptimisticUpdates })

        message.optimisticRollback = {
            count: { exists: true, value: 1 },
        }

        message.addAction(createAction({ optimistic: false }))

        message.invokeOnError({
            response: { status: 500 },
            body: '',
            preventDefault: () => {},
        })

        expect(rollbackOptimisticUpdates).not.toHaveBeenCalled()
    })
})
