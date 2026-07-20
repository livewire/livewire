import { describe, it, expect, beforeEach } from 'vitest'
import { registerSynth, flushSynths, dehydrateTree, hydrateValue, findSynthByValue } from './synths'
import { extractData, deeplyEqual, diff, diffAndConsolidate, diffAndPatchRecursive } from './utils'

class Money {
    constructor(amount, currency) {
        this.amount = amount
        this.currency = currency
    }

    formatted() {
        return `${this.amount / 100} ${this.currency}`
    }
}

let registerDateSynth = () => registerSynth('cbn', {
    match: (value) => value instanceof Date,
    hydrate: (value) => new Date(value),
    dehydrate: (value) => value.toISOString(),
})

let registerMoneySynth = () => registerSynth('money', {
    match: (value) => value instanceof Money,
    hydrate: (value) => new Money(value.amount, value.currency),
    dehydrate: (value) => ({ amount: value.amount, currency: value.currency }),
})

describe('registerSynth', () => {
    beforeEach(() => flushSynths())

    it('requires a string key', () => {
        expect(() => registerSynth(null, {})).toThrow()
    })

    it('requires match, hydrate, and dehydrate functions', () => {
        expect(() => registerSynth('foo', {})).toThrow()
        expect(() => registerSynth('foo', { match: () => {}, hydrate: () => {} })).toThrow()
    })

    it('allows an optional bind function', () => {
        let synth = { match: () => {}, hydrate: () => {}, dehydrate: () => {} }

        expect(() => registerSynth('foo', { ...synth, bind: () => {} })).not.toThrow()
        expect(() => registerSynth('bar', { ...synth, bind: 'nope' })).toThrow()
    })
})

describe('extractData', () => {
    beforeEach(() => flushSynths())

    it('hydrates tuples whose synth key has a registered synth', () => {
        registerDateSynth()

        let data = extractData({
            title: 'Hello',
            date: ['2021-01-01T00:00:00+00:00', { s: 'cbn', type: 'carbon' }],
        })

        expect(data.title).toBe('Hello')
        expect(data.date).toBeInstanceOf(Date)
        expect(data.date.toISOString()).toBe('2021-01-01T00:00:00.000Z')
    })

    it('leaves tuples without a registered synth as raw values', () => {
        let data = extractData({
            date: ['2021-01-01T00:00:00+00:00', { s: 'cbn', type: 'carbon' }],
        })

        expect(data.date).toBe('2021-01-01T00:00:00+00:00')
    })

    it('passes the full metadata to hydrate', () => {
        let receivedMeta

        registerSynth('cbn', {
            match: (value) => value instanceof Date,
            hydrate: (value, meta) => {
                receivedMeta = meta

                return new Date(value)
            },
            dehydrate: (value) => value.toISOString(),
        })

        extractData({ date: ['2021-01-01T00:00:00+00:00', { s: 'cbn', type: 'carbon' }] })

        expect(receivedMeta).toEqual({ s: 'cbn', type: 'carbon' })
    })

    it('hydrates children before parents', () => {
        registerMoneySynth()

        let data = extractData({
            prices: [
                {
                    first: [{ amount: [100, { s: 'int' }], currency: 'USD' }, { s: 'money' }],
                },
                { s: 'arr' },
            ],
        })

        expect(data.prices.first).toBeInstanceOf(Money)
        expect(data.prices.first.amount).toBe(100)
    })
})

describe('dehydrateTree', () => {
    beforeEach(() => flushSynths())

    it('returns the value untouched when no synths are registered', () => {
        let tree = { foo: 'bar' }

        expect(dehydrateTree(tree)).toBe(tree)
    })

    it('converts rich values back to their wire format without mutating the original tree', () => {
        registerDateSynth()

        let date = new Date('2021-01-01T00:00:00Z')
        let tree = { nested: { date }, list: [date] }

        let raw = dehydrateTree(tree)

        expect(raw.nested.date).toBe('2021-01-01T00:00:00.000Z')
        expect(raw.list[0]).toBe('2021-01-01T00:00:00.000Z')
        expect(tree.nested.date).toBe(date)
    })
})

describe('deeplyEqual', () => {
    beforeEach(() => flushSynths())

    it('compares rich values by their dehydrated wire format', () => {
        registerDateSynth()

        expect(deeplyEqual(new Date('2021-01-01T00:00:00Z'), new Date('2021-01-01T00:00:00Z'))).toBe(true)
        expect(deeplyEqual(new Date('2021-01-01T00:00:00Z'), new Date('2022-02-02T00:00:00Z'))).toBe(false)
    })

    it('compares a rich value against its raw wire format', () => {
        registerDateSynth()

        expect(deeplyEqual(new Date('2021-01-01T00:00:00Z'), '2021-01-01T00:00:00.000Z')).toBe(true)
    })
})

describe('diffing rich values', () => {
    beforeEach(() => flushSynths())

    it('detects no changes between equal rich values', () => {
        registerDateSynth()

        let left = { date: new Date('2021-01-01T00:00:00Z') }
        let right = { date: new Date('2021-01-01T00:00:00Z') }

        expect(diffAndConsolidate(left, right)).toEqual({})
        expect(diff(left, right)).toEqual({})
    })

    it('emits changed rich values in their dehydrated wire format', () => {
        registerDateSynth()

        let left = { date: new Date('2021-01-01T00:00:00Z') }
        let right = { date: new Date('2022-02-02T00:00:00Z') }

        expect(diffAndConsolidate(left, right)).toEqual({ date: '2022-02-02T00:00:00.000Z' })
        expect(diff(left, right)).toEqual({ date: '2022-02-02T00:00:00.000Z' })
    })

    it('treats rich values atomically instead of diffing their internals', () => {
        registerMoneySynth()

        let left = { price: new Money(100, 'USD') }
        let right = { price: new Money(200, 'USD') }

        expect(diffAndConsolidate(left, right)).toEqual({ price: { amount: 200, currency: 'USD' } })
    })

    it('dehydrates rich values nested inside consolidated diffs', () => {
        registerDateSynth()

        let left = { dates: [new Date('2021-01-01T00:00:00Z')] }
        let right = { dates: [new Date('2021-01-01T00:00:00Z'), new Date('2022-02-02T00:00:00Z')] }

        // The array grew, so the diff consolidates to the parent level and
        // must contain wire-format values, not rich ones...
        expect(diffAndConsolidate(left, right)).toEqual({
            dates: ['2021-01-01T00:00:00.000Z', '2022-02-02T00:00:00.000Z'],
        })
    })
})

describe('diffAndPatchRecursive with rich values', () => {
    beforeEach(() => flushSynths())

    it('preserves the identity of unchanged rich values', () => {
        registerDateSynth()

        let date = new Date('2021-01-01T00:00:00Z')
        let target = { date }
        let left = { date: new Date('2021-01-01T00:00:00Z') }
        let right = { date: new Date('2021-01-01T00:00:00Z') }

        diffAndPatchRecursive(left, right, target)

        expect(target.date).toBe(date)
    })

    it('treats an unchanged rich value as equal to its raw wire format', () => {
        registerDateSynth()

        let date = new Date('2021-01-01T00:00:00Z')
        let target = { date }
        // Simulates an applied update where the sent value was raw...
        let left = { date: '2021-01-01T00:00:00.000Z' }
        let right = { date: new Date('2021-01-01T00:00:00Z') }

        diffAndPatchRecursive(left, right, target)

        expect(target.date).toBe(date)
    })

    it('replaces changed rich values atomically', () => {
        registerMoneySynth()

        let target = { price: new Money(100, 'USD') }
        let left = { price: new Money(100, 'USD') }
        let right = { price: new Money(200, 'EUR') }

        diffAndPatchRecursive(left, right, target)

        expect(target.price).toBe(right.price)
        expect(target.price.formatted()).toBe('2 EUR')
    })
})

describe('findSynthByValue', () => {
    beforeEach(() => flushSynths())

    it('only consults match for the values a synth recognizes', () => {
        registerDateSynth()
        registerMoneySynth()

        expect(findSynthByValue(new Money(1, 'USD'))).toBeDefined()
        expect(findSynthByValue({ amount: 1, currency: 'USD' })).toBeUndefined()
        expect(findSynthByValue('string')).toBeUndefined()
        expect(findSynthByValue(null)).toBeUndefined()
    })
})

describe('hydrateValue', () => {
    beforeEach(() => flushSynths())

    it('passes values through when meta is missing or unregistered', () => {
        expect(hydrateValue('foo', undefined)).toBe('foo')
        expect(hydrateValue('foo', { s: 'cbn' })).toBe('foo')
    })
})

describe('hydration context', () => {
    beforeEach(() => flushSynths())

    it('passes the component and state path to hydrate', () => {
        let contexts = []

        registerSynth('cbn', {
            match: (value) => value instanceof Date,
            hydrate: (value, meta, context) => {
                contexts.push(context)

                return new Date(value)
            },
            dehydrate: (value) => value.toISOString(),
        })

        let component = { id: 'fake' }

        extractData({
            date: ['2021-01-01T00:00:00+00:00', { s: 'cbn' }],
            items: [
                [['2022-02-02T00:00:00+00:00', { s: 'cbn' }]],
                { s: 'arr' },
            ],
        }, { component })

        expect(contexts[0]).toEqual({ component, path: 'date' })
        expect(contexts[1]).toEqual({ component, path: 'items.0' })
    })

    it('passes no context when extractData is called without one', () => {
        let receivedContext = 'unset'

        registerSynth('cbn', {
            match: (value) => value instanceof Date,
            hydrate: (value, meta, context) => {
                receivedContext = context

                return new Date(value)
            },
            dehydrate: (value) => value.toISOString(),
        })

        extractData({ date: ['2021-01-01T00:00:00+00:00', { s: 'cbn' }] })

        expect(receivedContext).toBeUndefined()
    })
})

describe('values that dehydrate to undefined (no wire representation)', () => {
    class PendingThing {
        constructor(serialized = null) { this.serialized = serialized }
    }

    let registerPendingSynth = () => registerSynth('thing', {
        match: (value) => value instanceof PendingThing,
        hydrate: (value) => new PendingThing(value),
        dehydrate: (value) => value.serialized ?? undefined,
    })

    beforeEach(() => flushSynths())

    it('never includes them in diffs', () => {
        registerPendingSynth()

        expect(diffAndConsolidate({ thing: null }, { thing: new PendingThing() })).toEqual({})
        expect(diff({ thing: null }, { thing: new PendingThing() })).toEqual({})
    })

    it('omits them from arrays instead of sending null', () => {
        registerPendingSynth()

        let tree = { things: [new PendingThing('a'), new PendingThing()] }

        expect(dehydrateTree(tree)).toEqual({ things: ['a'] })
    })

    it('drops consolidated diffs that only differ by pending values', () => {
        registerPendingSynth()

        let left = { things: [new PendingThing('a')] }
        let right = { things: [new PendingThing('a'), new PendingThing()] }

        expect(diffAndConsolidate(left, right)).toEqual({})
    })

    it('still diffs settled values alongside pending ones', () => {
        registerPendingSynth()

        let left = { things: [new PendingThing('a')] }
        let right = { things: [new PendingThing('b'), new PendingThing()] }

        expect(diffAndConsolidate(left, right)).toEqual({ things: ['b'] })
    })
})
