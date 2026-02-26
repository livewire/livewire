import { describe, it, expect } from 'vitest'
import Action from './action'

function createComponent(memo = {}) {
    return {
        id: 'component-id',
        snapshot: {
            memo,
        },
    }
}

describe('Action optimism detection', () => {
    it('detects optimistic methods from component memo', () => {
        let action = new Action(createComponent({ optimistic: ['save'] }), 'save')

        expect(action.isOptimistic()).toBe(true)
    })

    it('detects optimistic modifier on directive origin', () => {
        let action = new Action(
            createComponent(),
            'save',
            [],
            {},
            { directive: { modifiers: ['optimistic'] } }
        )

        expect(action.isOptimistic()).toBe(true)
    })

    it('detects optimistic metadata on action', () => {
        let action = new Action(createComponent(), 'save', [], { optimistic: true })

        expect(action.isOptimistic()).toBe(true)
    })
})
