import { describe, it, expect } from 'vitest'
import { getNoncesFromCspHeaders, replaceNoncesInHtml } from './utils'

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
