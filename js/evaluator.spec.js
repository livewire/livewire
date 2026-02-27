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

    it('$-prefixed Alpine scope keys should not prevent $wire prefixing', () => {
        // When Alpine plugins (e.g. Filament) expose $set/$get on the data stack,
        // those $-prefixed keys should NOT be added to the SKIP list.
        // Livewire's $set must still be contextualized to $wire.$set.
        let mockEl = {
            _x_dataStack: [{ open: true, $set: () => {}, $get: () => {} }],
            hasAttribute: () => false,
            parentElement: null,
        }

        // $set should route to $wire.$set, not Alpine's $set
        expect(contextualizeExpression("$set('foo', 'bar')", mockEl)).toBe("$wire.$set('foo', 'bar')")
        // $get should also route to $wire.$get
        expect(contextualizeExpression("$get('foo')", mockEl)).toBe("$wire.$get('foo')")
        // $toggle should also route to $wire.$toggle
        expect(contextualizeExpression("$toggle('active')", mockEl)).toBe("$wire.$toggle('active')")
        // Regular Alpine keys should still be skipped (not prefixed)
        expect(contextualizeExpression('open', mockEl)).toBe('open')
    })

    it('$-prefixed keys on parent scope should not prevent $wire prefixing', () => {
        let parentEl = {
            _x_dataStack: [{ $set: () => {}, $get: () => {} }],
            hasAttribute: () => false,
            parentElement: null,
        }

        let childEl = {
            _x_dataStack: [{ item: {} }],
            hasAttribute: () => false,
            parentElement: parentEl,
        }

        expect(contextualizeExpression("$set('foo', 'bar')", childEl)).toBe("$wire.$set('foo', 'bar')")
        expect(contextualizeExpression('item', childEl)).toBe('item')
    })

    it('stops at livewire component root', () => {
        let rootEl = {
            _x_dataStack: [{ outsideVar: {} }],
            hasAttribute: (attr) => attr === 'wire:id',
            parentElement: {
                _x_dataStack: [{ aboveComponent: {} }],
                hasAttribute: () => false,
                parentElement: null,
            },
        }

        let childEl = {
            _x_dataStack: [{ innerVar: {} }],
            hasAttribute: () => false,
            parentElement: rootEl,
        }

        // innerVar and outsideVar are within the component, so they're skipped
        expect(contextualizeExpression('innerVar', childEl)).toBe('innerVar')
        expect(contextualizeExpression('outsideVar', childEl)).toBe('outsideVar')
        // aboveComponent is above the wire:id root, so it gets prefixed
        expect(contextualizeExpression('aboveComponent', childEl)).toBe('$wire.aboveComponent')
    })
})
