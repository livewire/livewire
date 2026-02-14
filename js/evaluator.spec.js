import { describe, it, expect } from 'vitest'
import { contextualizeExpression } from './evaluator'

describe('Contextualize expressions', () => {
    it('basic expressions', () => {
        expect(contextualizeExpression('foo')).toBe('$wire.foo')
        expect(contextualizeExpression('foo.bar')).toBe('$wire.foo.bar')
        expect(contextualizeExpression('foo.bar.baz')).toBe('$wire.foo.bar.baz')
        expect(contextualizeExpression('foo[0].bar')).toBe('$wire.foo[0].bar')
        expect(contextualizeExpression("foo['bar']")).toBe('$wire.foo[\'bar\']')
        expect(contextualizeExpression("foo['bar']['baz']")).toBe('$wire.foo[\'bar\'][\'baz\']')
        expect(contextualizeExpression("foo['bar'][0]")).toBe('$wire.foo[\'bar\'][0]')
    })

    it('negations', () => {
        expect(contextualizeExpression('!foo')).toBe('!$wire.foo')
        expect(contextualizeExpression('! foo')).toBe('! $wire.foo')
    })

    it('comparisons', () => {
        expect(contextualizeExpression('foo > 1')).toBe('$wire.foo > 1')
        expect(contextualizeExpression('foo == 1')).toBe('$wire.foo == 1')
        expect(contextualizeExpression('foo != 1')).toBe('$wire.foo != 1')
        expect(contextualizeExpression('foo === 1')).toBe('$wire.foo === 1')
        expect(contextualizeExpression('foo !== 1')).toBe('$wire.foo !== 1')
        expect(contextualizeExpression('foo > bar')).toBe('$wire.foo > $wire.bar')
        expect(contextualizeExpression('foo == bar')).toBe('$wire.foo == $wire.bar')
        expect(contextualizeExpression('foo != bar')).toBe('$wire.foo != $wire.bar')
        expect(contextualizeExpression('foo === bar')).toBe('$wire.foo === $wire.bar')
        expect(contextualizeExpression('foo !== bar')).toBe('$wire.foo !== $wire.bar')
    })

    it('$set', () => {
        expect(contextualizeExpression("$set('foo.bar', baz)")).toBe('$wire.$set(\'foo.bar\', $wire.baz)')
    })

    it('object literals', () => {
        expect(contextualizeExpression("{ foo: foo }")).toBe('{ foo: $wire.foo }')
        expect(contextualizeExpression("{ fooBar: foo }")).toBe('{ fooBar: $wire.foo }')
    })

    it('x-for loop variables are skipped', () => {
        // Create a mock element with x-for attribute
        let mockEl = {
            nodeType: 1,
            getAttribute: (attr) => attr === 'x-for' ? 'user in users' : null,
            parentElement: null
        }

        expect(contextualizeExpression('user', mockEl)).toBe('user')
        expect(contextualizeExpression('user.name', mockEl)).toBe('user.name')
        expect(contextualizeExpression('doSomething(user)', mockEl)).toBe('$wire.doSomething(user)')
    })

    it('x-for with index variable', () => {
        let mockEl = {
            nodeType: 1,
            getAttribute: (attr) => attr === 'x-for' ? '(user, index) in users' : null,
            parentElement: null
        }

        expect(contextualizeExpression('user', mockEl)).toBe('user')
        expect(contextualizeExpression('index', mockEl)).toBe('index')
        expect(contextualizeExpression('users', mockEl)).toBe('$wire.users')
    })

    it('x-for with all variables (item, index, collection)', () => {
        let mockEl = {
            nodeType: 1,
            getAttribute: (attr) => attr === 'x-for' ? '(value, key, collection) in items' : null,
            parentElement: null
        }

        expect(contextualizeExpression('value', mockEl)).toBe('value')
        expect(contextualizeExpression('key', mockEl)).toBe('key')
        expect(contextualizeExpression('collection', mockEl)).toBe('collection')
        expect(contextualizeExpression('items', mockEl)).toBe('$wire.items')
    })

    it('nested x-for loops', () => {
        let childEl = {
            nodeType: 1,
            getAttribute: (attr) => attr === 'x-for' ? 'item in items' : null,
            parentElement: {
                nodeType: 1,
                getAttribute: (attr) => attr === 'x-for' ? 'user in users' : null,
                parentElement: null
            }
        }

        expect(contextualizeExpression('item', childEl)).toBe('item')
        expect(contextualizeExpression('user', childEl)).toBe('user')
        expect(contextualizeExpression('items', childEl)).toBe('$wire.items')
        expect(contextualizeExpression('users', childEl)).toBe('$wire.users')
    })

    it('only loop variables are skipped, not Alpine x-data scope', () => {
        // This is the key test: Alpine scope variables that aren't loop variables
        // should still get prefixed with $wire, allowing intentional shadowing
        let mockEl = {
            nodeType: 1,
            getAttribute: (attr) => attr === 'x-for' ? 'item in items' : null,
            parentElement: null
        }

        // 'item' is a loop variable, so it's skipped
        expect(contextualizeExpression('item', mockEl)).toBe('item')

        // But other identifiers (even if they exist in Alpine x-data scope)
        // should be prefixed, allowing Livewire properties to shadow them
        expect(contextualizeExpression('someProperty', mockEl)).toBe('$wire.someProperty')
    })
})
