import { describe, it, expect } from 'vitest'
import { filesFromEvent, getNoncesFromCspHeaders, isEmpty, replaceNoncesInHtml } from './utils'

describe('filesFromEvent', () => {
    it('returns files from a standalone file paste', () => {
        let file = new File(['image'], 'image.png', { type: 'image/png' })

        expect(filesFromEvent({
            clipboardData: { files: [file], types: ['Files'] },
        })).toEqual([file])
    })

    it('ignores embedded files when the clipboard also contains rich text', () => {
        let file = new File(['image'], 'image.png', { type: 'image/png' })

        expect(filesFromEvent({
            clipboardData: {
                files: [file],
                types: ['text/plain', 'text/html', 'Files'],
            },
        })).toEqual([])
    })
})

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

describe('getNoncesFromCspHeaders', () => {
    it('reads nonces from enforced and report-only policies', () => {
        let headers = new Headers({
            'Content-Security-Policy': "script-src 'nonce-abc' 'strict-dynamic'; style-src 'nonce-def'",
            'Content-Security-Policy-Report-Only': "script-src 'nonce-ghi'",
        })

        expect(getNoncesFromCspHeaders(headers)).toEqual(['abc', 'def', 'ghi'])
    })

    it('returns nothing when the response has no policy', () => {
        expect(getNoncesFromCspHeaders(new Headers)).toEqual([])
    })
})

describe('replaceNoncesInHtml', () => {
    it('only rewrites the nonces it is given', () => {
        window.livewireScriptConfig = { nonce: 'page-nonce' }

        let html = '<script nonce="response-nonce">rewritten()</script><script nonce="other-nonce">ignored()</script>'

        expect(replaceNoncesInHtml(html, ['response-nonce'])).toBe(
            '<script nonce="page-nonce">rewritten()</script><script nonce="other-nonce">ignored()</script>'
        )
    })
})
