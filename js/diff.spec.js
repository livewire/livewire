import { describe, it, expect } from 'vitest'
import { diff, diffAndConsolidate } from './utils'

describe('diff (legacy)', () => {
    it('detects no changes', () => {
        expect(diff({ foo: 'bar' }, { foo: 'bar' })).toEqual({})
    })

    it('detects simple property changes', () => {
        expect(diff({ foo: 'bar' }, { foo: 'baz' })).toEqual({ foo: 'baz' })
    })

    it('detects nested property changes', () => {
        expect(diff({ foo: { bar: 'baz' } }, { foo: { bar: 'qux' } })).toEqual({ 'foo.bar': 'qux' })
    })

    it('marks removed items with __rm__', () => {
        expect(diff({ items: ['a', 'b', 'c'] }, { items: ['a'] })).toEqual({
            'items.1': '__rm__',
            'items.2': '__rm__'
        })
    })

    it('generates granular diffs for array replacements', () => {
        expect(diff(
            { items: ['one', 'two', 'three', 'four'] },
            { items: ['two', 'three'] }
        )).toEqual({
            'items.0': 'two',
            'items.1': 'three',
            'items.2': '__rm__',
            'items.3': '__rm__'
        })
    })
})

describe('diffAndConsolidate', () => {
    it('detects no changes', () => {
        expect(diffAndConsolidate({ foo: 'bar' }, { foo: 'bar' })).toEqual({})
    })

    it('detects simple property changes', () => {
        expect(diffAndConsolidate({ foo: 'bar' }, { foo: 'baz' })).toEqual({ foo: 'baz' })
    })

    it('detects nested property changes without consolidation for single item', () => {
        expect(diffAndConsolidate(
            { foo: { bar: 'baz', qux: 'unchanged' } },
            { foo: { bar: 'changed', qux: 'unchanged' } }
        )).toEqual({ 'foo.bar': 'changed' })
    })

    it('consolidates when array size decreases', () => {
        expect(diffAndConsolidate(
            { items: ['one', 'two', 'three', 'four'] },
            { items: ['two', 'three'] }
        )).toEqual({
            items: ['two', 'three']
        })
    })

    it('consolidates when array size increases', () => {
        expect(diffAndConsolidate(
            { items: ['a', 'b'] },
            { items: ['a', 'b', 'c', 'd'] }
        )).toEqual({
            items: ['a', 'b', 'c', 'd']
        })
    })

    it('consolidates when all items in array change', () => {
        expect(diffAndConsolidate(
            { items: ['one', 'two', 'three'] },
            { items: ['a', 'b', 'c'] }
        )).toEqual({
            items: ['a', 'b', 'c']
        })
    })

    it('does not consolidate when only some items change', () => {
        expect(diffAndConsolidate(
            { items: ['one', 'two', 'three'] },
            { items: ['one', 'changed', 'three'] }
        )).toEqual({
            'items.1': 'changed'
        })
    })

    it('consolidates nested arrays when size changes', () => {
        expect(diffAndConsolidate(
            { foo: { bar: ['a', 'b', 'c'] } },
            { foo: { bar: ['x', 'y'] } }
        )).toEqual({
            'foo.bar': ['x', 'y']
        })
    })

    it('consolidates at the deepest level that qualifies', () => {
        expect(diffAndConsolidate(
            { foo: { bar: ['a', 'b'], baz: 'unchanged' } },
            { foo: { bar: ['x', 'y', 'z'], baz: 'unchanged' } }
        )).toEqual({
            'foo.bar': ['x', 'y', 'z']
        })
    })

    it('handles empty arrays', () => {
        expect(diffAndConsolidate(
            { items: ['a', 'b', 'c'] },
            { items: [] }
        )).toEqual({
            items: []
        })
    })

    it('handles array becoming populated', () => {
        expect(diffAndConsolidate(
            { items: [] },
            { items: ['a', 'b'] }
        )).toEqual({
            items: ['a', 'b']
        })
    })

    it('handles objects with changing keys', () => {
        expect(diffAndConsolidate(
            { data: { a: 1, b: 2 } },
            { data: { c: 3, d: 4 } }
        )).toEqual({
            data: { c: 3, d: 4 }
        })
    })

    it('handles mixed object/array changes', () => {
        expect(diffAndConsolidate(
            { items: ['a', 'b'], other: 'unchanged' },
            { items: ['x'], other: 'unchanged' }
        )).toEqual({
            items: ['x']
        })
    })

    it('handles deeply nested structures', () => {
        expect(diffAndConsolidate(
            { a: { b: { c: { d: ['one', 'two'] } } } },
            { a: { b: { c: { d: ['three'] } } } }
        )).toEqual({
            'a.b.c.d': ['three']
        })
    })

    it('handles multiple independent changes', () => {
        expect(diffAndConsolidate(
            { foo: 'a', bar: 'b', baz: 'c' },
            { foo: 'x', bar: 'b', baz: 'z' }
        )).toEqual({
            foo: 'x',
            baz: 'z'
        })
    })

    it('handles type changes (array to primitive)', () => {
        expect(diffAndConsolidate(
            { foo: ['a', 'b'] },
            { foo: 'string' }
        )).toEqual({
            foo: 'string'
        })
    })

    it('handles type changes (primitive to array)', () => {
        expect(diffAndConsolidate(
            { foo: 'string' },
            { foo: ['a', 'b'] }
        )).toEqual({
            foo: ['a', 'b']
        })
    })

    it('handles null values', () => {
        expect(diffAndConsolidate(
            { foo: ['a', 'b'] },
            { foo: null }
        )).toEqual({
            foo: null
        })
    })

    it('handles undefined becoming defined', () => {
        expect(diffAndConsolidate(
            { foo: undefined },
            { foo: 'bar' }
        )).toEqual({
            foo: 'bar'
        })
    })

    it('consolidates object when all properties change', () => {
        expect(diffAndConsolidate(
            { config: { a: 1, b: 2, c: 3 } },
            { config: { a: 'x', b: 'y', c: 'z' } }
        )).toEqual({
            'config': { a: 'x', b: 'y', c: 'z' }
        })
    })

    it('does not consolidate object when only some properties change', () => {
        expect(diffAndConsolidate(
            { config: { a: 1, b: 2, c: 3 } },
            { config: { a: 'x', b: 2, c: 3 } }
        )).toEqual({
            'config.a': 'x'
        })
    })

    it('handles arrays of objects', () => {
        expect(diffAndConsolidate(
            { users: [{ name: 'Alice' }, { name: 'Bob' }] },
            { users: [{ name: 'Charlie' }] }
        )).toEqual({
            users: [{ name: 'Charlie' }]
        })
    })

    it('handles partial changes in arrays of objects', () => {
        expect(diffAndConsolidate(
            { users: [{ name: 'Alice', age: 30 }, { name: 'Bob', age: 25 }] },
            { users: [{ name: 'Alice', age: 31 }, { name: 'Bob', age: 25 }] }
        )).toEqual({
            'users.0.age': 31
        })
    })

    it('handles numeric string keys in objects', () => {
        expect(diffAndConsolidate(
            { data: { '0': 'a', '1': 'b' } },
            { data: { '0': 'x' } }
        )).toEqual({
            data: { '0': 'x' }
        })
    })

    it('preserves dot notation when empty array becomes object with nested properties', () => {
        // This is the scenario from wire:model.live="tableFilters.filter_1.value"
        // where tableFilters starts as [] and becomes { filter_1: { value: 'foo' } }
        expect(diffAndConsolidate(
            { tableFilters: [] },
            { tableFilters: { filter_1: { value: 'foo' } } }
        )).toEqual({
            'tableFilters.filter_1.value': 'foo'
        })
    })

    it('handles arrays with non-numeric (string) keys', () => {
        // When JS sets arr['filter_1'] = value on an array, it adds a string property.
        // JSON.stringify ignores string keys on arrays, so we need granular diffs.
        let leftArr = []
        let rightArr = []
        rightArr['filter_1'] = { value: 'foo' }

        expect(diffAndConsolidate(
            { tableFilters: leftArr },
            { tableFilters: rightArr }
        )).toEqual({
            'tableFilters.filter_1.value': 'foo'
        })
    })

    it('does not consolidate single property changes', () => {
        // Single property changes should remain granular for wire:target to work
        // e.g., wire:target="form.text" needs "form.text" not "form"
        expect(diffAndConsolidate(
            { form: { text: '' } },
            { form: { text: 'Text' } }
        )).toEqual({
            'form.text': 'Text'
        })
    })

    it('detects key order changes in objects', () => {
        expect(diffAndConsolidate(
            { data: { a: 1, b: 2 } },
            { data: { b: 2, a: 1 } }
        )).toEqual({
            data: { b: 2, a: 1 }
        })
    })

    it('ignores key order changes at root level', () => {
        expect(diffAndConsolidate(
            { a: 1, b: 2 },
            { b: 2, a: 1 }
        )).toEqual({})
    })

    it('detects key order changes in deeply nested objects', () => {
        expect(diffAndConsolidate(
            { a: { b: { c: 1, d: 2 } } },
            { a: { b: { d: 2, c: 1 } } }
        )).toEqual({
            'a.b': { d: 2, c: 1 }
        })
    })

    it('detects key order changes with simultaneous value changes', () => {
        expect(diffAndConsolidate(
            { data: { a: 1, b: 2 } },
            { data: { b: 3, a: 1 } }
        )).toEqual({
            data: { b: 3, a: 1 }
        })
    })
})
