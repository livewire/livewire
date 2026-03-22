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

    it('alpine scope variables are skipped', () => {
        let mockEl = {
            _x_dataStack: [{ user: {}, index: 0 }],
            hasAttribute: () => false,
            parentElement: null,
        }

        expect(contextualizeExpression('user', mockEl)).toBe('user')
        expect(contextualizeExpression('user.name', mockEl)).toBe('user.name')
        expect(contextualizeExpression('doSomething(user)', mockEl)).toBe('$wire.doSomething(user)')
        expect(contextualizeExpression('index', mockEl)).toBe('index')
    })

    it('nested alpine scopes', () => {
        let parentEl = {
            _x_dataStack: [{ user: {} }],
            hasAttribute: () => false,
            parentElement: null,
        }

        let childEl = {
            _x_dataStack: [{ item: {} }],
            hasAttribute: () => false,
            parentElement: parentEl,
        }

        expect(contextualizeExpression('item', childEl)).toBe('item')
        expect(contextualizeExpression('user', childEl)).toBe('user')
        expect(contextualizeExpression('other', childEl)).toBe('$wire.other')
    })

    it('$-prefixed alpine scope keys are not skipped', () => {
        let mockEl = {
            _x_dataStack: [{ $set: () => {} }],
            hasAttribute: () => false,
            parentElement: null,
        }

        expect(contextualizeExpression("$set('foo', 'bar')", mockEl)).toBe('$wire.$set(\'foo\', \'bar\')')
    })

})
