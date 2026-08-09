import { describe, it, expect } from 'vitest'
import { Effects } from './effects'

describe('Effects', () => {
    describe('constructor', () => {
        it('creates an empty effects instance by default', () => {
            let effects = new Effects()

            expect(effects).toBeInstanceOf(Effects)
            expect(Object.keys(effects)).toEqual([])
        })

        it('assigns own properties from a plain object', () => {
            let effects = new Effects({ html: '<div></div>', redirect: '/home' })

            expect(effects.html).toBe('<div></div>')
            expect(effects.redirect).toBe('/home')
        })

        it('ignores non-object data', () => {
            expect(Object.keys(new Effects(null))).toEqual([])
            expect(Object.keys(new Effects(undefined))).toEqual([])
            expect(Object.keys(new Effects('string'))).toEqual([])
            expect(Object.keys(new Effects(42))).toEqual([])
            expect(Object.keys(new Effects(true))).toEqual([])
        })

        it('does not copy inherited properties', () => {
            let proto = { inherited: true }
            let data = Object.create(proto)
            data.own = 'value'

            let effects = new Effects(data)

            expect(effects.own).toBe('value')
            expect(effects.inherited).toBeUndefined()
            expect(effects.has('inherited')).toBe(false)
        })
    })

    describe('has', () => {
        it('returns true for an own key that exists', () => {
            let effects = new Effects({ redirect: '/home' })

            expect(effects.has('redirect')).toBe(true)
        })

        it('returns false for a missing key', () => {
            let effects = new Effects({ redirect: '/home' })

            expect(effects.has('html')).toBe(false)
        })

        it('returns true for falsy own values', () => {
            let effects = new Effects({
                empty: '',
                zero: 0,
                no: false,
                nothing: null,
                missing: undefined,
            })

            expect(effects.has('empty')).toBe(true)
            expect(effects.has('zero')).toBe(true)
            expect(effects.has('no')).toBe(true)
            expect(effects.has('nothing')).toBe(true)
            expect(effects.has('missing')).toBe(true)
        })

        it('does not match prototype properties', () => {
            let effects = new Effects({})

            expect(effects.has('hasOwnProperty')).toBe(false)
            expect(effects.has('toString')).toBe(false)
            expect(effects.has('constructor')).toBe(false)
        })
    })

    describe('hasValue', () => {
        it('returns true for a truthy own value', () => {
            let effects = new Effects({ html: '<div></div>', listeners: ['foo'] })

            expect(effects.hasValue('html')).toBe(true)
            expect(effects.hasValue('listeners')).toBe(true)
        })

        it('returns false for a missing key', () => {
            let effects = new Effects({ html: '<div></div>' })

            expect(effects.hasValue('redirect')).toBe(false)
        })

        it('returns false for falsy own values', () => {
            let effects = new Effects({
                empty: '',
                zero: 0,
                no: false,
                nothing: null,
                missing: undefined,
            })

            expect(effects.hasValue('empty')).toBe(false)
            expect(effects.hasValue('zero')).toBe(false)
            expect(effects.hasValue('no')).toBe(false)
            expect(effects.hasValue('nothing')).toBe(false)
            expect(effects.hasValue('missing')).toBe(false)
        })

        it('returns true for objects and arrays (including empty ones)', () => {
            // !!{} and !![] are both true in JS, so hasValue treats them as present values
            let effects = new Effects({
                obj: {},
                emptyItems: [],
                items: ['a'],
            })

            expect(effects.hasValue('obj')).toBe(true)
            expect(effects.hasValue('emptyItems')).toBe(true)
            expect(effects.hasValue('items')).toBe(true)
        })
    })

    describe('property access', () => {
        it('allows direct property reads used throughout the codebase', () => {
            let effects = new Effects({
                redirect: '/dashboard',
                html: '<span>hi</span>',
                dispatches: [{ name: 'saved' }],
            })

            expect(effects.redirect).toBe('/dashboard')
            expect(effects.html).toBe('<span>hi</span>')
            expect(effects.dispatches).toEqual([{ name: 'saved' }])
        })
    })

    describe('toJSON', () => {
        it('returns a plain object of own data properties', () => {
            let effects = new Effects({ html: '<div></div>', redirect: '/home' })

            expect(effects.toJSON()).toEqual({
                html: '<div></div>',
                redirect: '/home',
            })
        })

        it('does not include class methods', () => {
            let effects = new Effects({ html: '<div></div>' })
            let json = effects.toJSON()

            expect(json.has).toBeUndefined()
            expect(json.hasValue).toBeUndefined()
            expect(json.toJSON).toBeUndefined()
        })

        it('works with JSON.stringify for wire:effects attributes', () => {
            let effects = new Effects({
                listeners: ['foo', 'bar'],
                url: { search: true },
            })

            expect(JSON.stringify(effects)).toBe(JSON.stringify({
                listeners: ['foo', 'bar'],
                url: { search: true },
            }))
        })

        it('round-trips through JSON for inscription', () => {
            let original = new Effects({
                listeners: ['saved'],
                scripts: { 'app.js': true },
            })

            let restored = new Effects(JSON.parse(JSON.stringify(original)))

            expect(restored.hasValue('listeners')).toBe(true)
            expect(restored.listeners).toEqual(['saved'])
            expect(restored.hasValue('scripts')).toBe(true)
            expect(restored.scripts).toEqual({ 'app.js': true })
            expect(restored.has('html')).toBe(false)
        })
    })

    describe('spread compatibility', () => {
        it('can be spread into a plain object without copying methods', () => {
            let effects = new Effects({ html: '<div></div>', redirect: '/home' })
            let plain = { ...effects }

            expect(plain).toEqual({ html: '<div></div>', redirect: '/home' })
            expect(plain.has).toBeUndefined()
            expect(plain.hasValue).toBeUndefined()
        })

        it('supports constructing a new Effects from a spread', () => {
            let effects = new Effects({ redirect: '/home' })
            let merged = new Effects({ ...effects, html: '<div></div>' })

            expect(merged.hasValue('redirect')).toBe(true)
            expect(merged.hasValue('html')).toBe(true)
            expect(merged.redirect).toBe('/home')
            expect(merged.html).toBe('<div></div>')
        })
    })
})
