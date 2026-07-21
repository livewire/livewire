import { describe, it, expect } from 'vitest'
import { isEmpty } from './utils'

describe('isEmpty', () => {
    it('mirrors PHP empty() for scalars', () => {
        expect(isEmpty(null)).toBe(true)
        expect(isEmpty(undefined)).toBe(true)
        expect(isEmpty(false)).toBe(true)
        expect(isEmpty(0)).toBe(true)
        expect(isEmpty('')).toBe(true)
        expect(isEmpty('0')).toBe(true)

        expect(isEmpty(true)).toBe(false)
        expect(isEmpty(1)).toBe(false)
        expect(isEmpty(-1)).toBe(false)
        expect(isEmpty('a')).toBe(false)
        expect(isEmpty(' ')).toBe(false)
        expect(isEmpty('0.0')).toBe(false)
        expect(isEmpty('false')).toBe(false)
    })

    it('treats arrays without items as empty', () => {
        expect(isEmpty([])).toBe(true)

        expect(isEmpty([0])).toBe(false)
        expect(isEmpty([null])).toBe(false)
        expect(isEmpty(['a'])).toBe(false)
    })

    it('treats objects without keys as empty (empty PHP associative arrays)', () => {
        expect(isEmpty({})).toBe(true)

        expect(isEmpty({ a: 1 })).toBe(false)
        expect(isEmpty({ a: null })).toBe(false)
    })
})
