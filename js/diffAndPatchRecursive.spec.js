import { describe, it, expect } from 'vitest'
import { diffAndPatchRecursive } from './utils'

describe('diffAndPatchRecursive', () => {
    it('does not modify target when nothing changed', () => {
        let target = { a: 1, b: 2 }
        diffAndPatchRecursive({ a: 1, b: 2 }, { a: 1, b: 2 }, target)
        expect(target).toEqual({ a: 1, b: 2 })
    })

    it('patches a changed value', () => {
        let target = { a: 1, b: 2 }
        diffAndPatchRecursive({ a: 1, b: 2 }, { a: 1, b: 99 }, target)
        expect(target).toEqual({ a: 1, b: 99 })
    })

    it('patches nested changes', () => {
        let target = { data: { a: 1, b: 2 } }
        diffAndPatchRecursive({ data: { a: 1, b: 2 } }, { data: { a: 1, b: 99 } }, target)
        expect(target).toEqual({ data: { a: 1, b: 99 } })
    })

    it('adds new keys', () => {
        let target = { a: 1 }
        diffAndPatchRecursive({ a: 1 }, { a: 1, b: 2 }, target)
        expect(target).toEqual({ a: 1, b: 2 })
    })

    it('removes deleted keys', () => {
        let target = { a: 1, b: 2, c: 3 }
        diffAndPatchRecursive({ a: 1, b: 2, c: 3 }, { a: 1 }, target)
        expect(target).toEqual({ a: 1 })
    })

    it('removes deleted array items via splice', () => {
        let target = ['a', 'b', 'c']
        diffAndPatchRecursive(['a', 'b', 'c'], ['a'], target)
        expect(target).toEqual(['a'])
    })

    it('preserves key order when keys are reordered', () => {
        let target = { a: 1, b: 2 }
        diffAndPatchRecursive({ a: 1, b: 2 }, { b: 2, a: 1 }, target)
        expect(Object.keys(target)).toEqual(['b', 'a'])
    })

    it('preserves key order when key is inserted in middle', () => {
        let target = { a: 1, b: 2, c: 3 }
        diffAndPatchRecursive({ a: 1, b: 2, c: 3 }, { a: 1, new: 'NEW', b: 2, c: 3 }, target)
        expect(Object.keys(target)).toEqual(['a', 'new', 'b', 'c'])
        expect(target).toEqual({ a: 1, new: 'NEW', b: 2, c: 3 })
    })

    it('does not reorder when key is appended at end', () => {
        let target = { a: 1, b: 2 }
        diffAndPatchRecursive({ a: 1, b: 2 }, { a: 1, b: 2, c: 3 }, target)
        expect(Object.keys(target)).toEqual(['a', 'b', 'c'])
        expect(target).toEqual({ a: 1, b: 2, c: 3 })
    })

    it('preserves ephemeral target state for unchanged keys', () => {
        let target = { a: 'ephemeral', b: 2 }
        diffAndPatchRecursive({ a: 1, b: 2 }, { a: 1, b: 99 }, target)
        expect(target).toEqual({ a: 'ephemeral', b: 99 })
    })

    it('recurses into nested objects preserving target references', () => {
        let nested = { x: 1, y: 2 }
        let target = { data: nested }
        diffAndPatchRecursive({ data: { x: 1, y: 2 } }, { data: { x: 1, y: 99 } }, target)
        expect(target.data).toBe(nested)
        expect(target.data.y).toBe(99)
    })

    it('handles dot-containing keys without corruption', () => {
        let target = { 'order.foo': { show: 'abc' } }
        diffAndPatchRecursive(
            { 'order.foo': { show: 'abc' } },
            { 'order.foo': { show: 'abc' }, 'order.foo.bar': { show: 'ghl' } },
            target
        )
        expect(target).toEqual({
            'order.foo': { show: 'abc' },
            'order.foo.bar': { show: 'ghl' },
        })
    })

    it('handles type change from object to primitive', () => {
        let target = { a: { nested: true } }
        diffAndPatchRecursive({ a: { nested: true } }, { a: 'string' }, target)
        expect(target).toEqual({ a: 'string' })
    })

    it('handles type change from primitive to object', () => {
        let target = { a: 'string' }
        diffAndPatchRecursive({ a: 'string' }, { a: { nested: true } }, target)
        expect(target).toEqual({ a: { nested: true } })
    })

    it('handles null left side', () => {
        let target = {}
        diffAndPatchRecursive(null, { a: 1, b: 2 }, target)
        expect(target).toEqual({ a: 1, b: 2 })
    })

    it('patches deeply nested changes (3+ levels)', () => {
        let target = { a: { b: { c: { d: 1 } } } }
        diffAndPatchRecursive({ a: { b: { c: { d: 1 } } } }, { a: { b: { c: { d: 99 } } } }, target)
        expect(target.a.b.c.d).toBe(99)
    })

    it('patches array item at specific index', () => {
        let target = ['a', 'b', 'c']
        diffAndPatchRecursive(['a', 'b', 'c'], ['a', 'changed', 'c'], target)
        expect(target).toEqual(['a', 'changed', 'c'])
    })

    it('adds items to end of array', () => {
        let target = ['a', 'b']
        diffAndPatchRecursive(['a', 'b'], ['a', 'b', 'c', 'd'], target)
        expect(target).toEqual(['a', 'b', 'c', 'd'])
    })

    it('removes multiple array items in correct order', () => {
        let target = ['a', 'b', 'c', 'd', 'e']
        diffAndPatchRecursive(['a', 'b', 'c', 'd', 'e'], ['a', 'c', 'e'], target)
        expect(target).toEqual(['a', 'c', 'e'])
    })

    it('removes key from middle of object', () => {
        let target = { a: 1, b: 2, c: 3 }
        diffAndPatchRecursive({ a: 1, b: 2, c: 3 }, { a: 1, c: 3 }, target)
        expect(target).toEqual({ a: 1, c: 3 })
    })

    it('handles objects nested inside arrays', () => {
        let target = [{ name: 'Alice' }, { name: 'Bob' }]
        diffAndPatchRecursive(
            [{ name: 'Alice' }, { name: 'Bob' }],
            [{ name: 'Alice' }, { name: 'Bobby' }],
            target
        )
        expect(target).toEqual([{ name: 'Alice' }, { name: 'Bobby' }])
    })

    it('handles arrays nested inside objects', () => {
        let target = { items: ['a', 'b'], other: 'unchanged' }
        diffAndPatchRecursive(
            { items: ['a', 'b'], other: 'unchanged' },
            { items: ['a', 'b', 'c'], other: 'unchanged' },
            target
        )
        expect(target).toEqual({ items: ['a', 'b', 'c'], other: 'unchanged' })
    })

    it('replaces target array when right becomes object', () => {
        let target = { data: ['a', 'b'] }
        diffAndPatchRecursive({ data: ['a', 'b'] }, { data: { key: 'value' } }, target)
        expect(target).toEqual({ data: { key: 'value' } })
    })

    it('replaces target object when right becomes array', () => {
        let target = { data: { key: 'value' } }
        diffAndPatchRecursive({ data: { key: 'value' } }, { data: ['a', 'b'] }, target)
        expect(target).toEqual({ data: ['a', 'b'] })
    })

    it('preserves key order when key is inserted at start', () => {
        let target = { b: 2, c: 3 }
        diffAndPatchRecursive({ b: 2, c: 3 }, { a: 1, b: 2, c: 3 }, target)
        expect(Object.keys(target)).toEqual(['a', 'b', 'c'])
    })
})
