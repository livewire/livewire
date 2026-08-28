import { describe, expect, it } from 'vitest'
import { mergeCleanState } from './mergeCleanState'

describe('mergeCleanState', () => {
    it('accepts server changes to clean properties', () => {
        expect(mergeCleanState(
            { title: 'Original', count: 0 },
            { title: 'Original', count: 0 },
            { title: 'Original', count: 1 },
        )).toEqual({ title: 'Original', count: 1 })
    })

    it('preserves baselines for local changes while accepting clean siblings', () => {
        expect(mergeCleanState(
            { form: { title: 'Original', status: 'draft' }, count: 0 },
            { form: { title: 'Changed', status: 'draft' }, count: 0 },
            { form: { title: 'Changed', status: 'published' }, count: 1 },
        )).toEqual({
            form: { title: 'Original', status: 'published' },
            count: 1,
        })
    })

    it('preserves a locally changed array as one value', () => {
        expect(mergeCleanState(
            { tags: ['one'], count: 0 },
            { tags: ['one', 'two'], count: 0 },
            { tags: ['one', 'two'], count: 1 },
        )).toEqual({ tags: ['one'], count: 1 })
    })

    it('preserves local additions and removals', () => {
        expect(mergeCleanState(
            { form: { kept: 'yes', removed: 'saved' } },
            { form: { kept: 'yes', added: 'draft' } },
            { form: { kept: 'changed', added: 'draft' } },
        )).toEqual({ form: { kept: 'changed', removed: 'saved' } })
    })
})
