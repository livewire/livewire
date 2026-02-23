import { beforeEach, describe, expect, it } from 'vitest'
import { getQueryParam, hasQueryParam, setQueryParam } from './url'

describe('History URL helpers', () => {
    beforeEach(() => {
        window.history.replaceState({}, '', '/livewire-test?foo=bar#section')
    })

    it('reads query params from the current URL', () => {
        expect(hasQueryParam('foo')).toBe(true)
        expect(getQueryParam('foo')).toBe('bar')
        expect(hasQueryParam('missing')).toBe(false)
        expect(getQueryParam('missing')).toBe(null)
    })

    it('sets query params while preserving existing params and hash', () => {
        setQueryParam('page', '2')

        expect(window.location.search).toBe('?foo=bar&page=2')
        expect(window.location.hash).toBe('#section')
    })

    it('updates an existing query param', () => {
        setQueryParam('foo', 'baz')

        expect(window.location.search).toBe('?foo=baz')
        expect(window.location.hash).toBe('#section')
    })
})
