import { describe, expect, it } from 'vitest'
import Action from './action'

function createComponent(memo = {}) {
    return {
        id: 'component-id',
        snapshot: {
            memo,
        },
    }
}

describe('Action offline queue detection', () => {
    it('detects offline queue methods from component memo', () => {
        let action = new Action(createComponent({ offlineQueue: ['save'] }), 'save')

        expect(action.isOfflineQueued()).toBe(true)
    })

    it('detects offline.queue modifiers on directive origin', () => {
        let action = new Action(
            createComponent(),
            'save',
            [],
            {},
            { directive: { modifiers: ['offline', 'queue'] } }
        )

        expect(action.isOfflineQueued()).toBe(true)
    })

    it('detects offline queue metadata on action', () => {
        let action = new Action(createComponent(), 'save', [], { offlineQueue: true })

        expect(action.isOfflineQueued()).toBe(true)
    })
})
