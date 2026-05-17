import { describe, expect, it } from 'vitest'
import { shouldHandleLinkWithNavigate } from './links'

function makeLink(attributes = {}) {
    let link = document.createElement('a')

    Object.entries(attributes).forEach(([name, value]) => {
        if (value === true) {
            link.setAttribute(name, '')

            return
        }

        link.setAttribute(name, value)
    })

    return link
}

describe('wire:navigate link handling', () => {
    it('handles same-origin relative links', () => {
        let link = makeLink({ href: '/posts' })

        expect(shouldHandleLinkWithNavigate(link)).toBe(true)
    })

    it('handles same-origin absolute links', () => {
        let link = makeLink({ href: `${window.location.origin}/posts` })

        expect(shouldHandleLinkWithNavigate(link)).toBe(true)
    })

    it('does not handle cross-origin links', () => {
        let link = makeLink({ href: 'https://example.com/posts' })

        expect(shouldHandleLinkWithNavigate(link)).toBe(false)
    })

    it('does not handle links with a non-self target', () => {
        let link = makeLink({ href: '/posts', target: '_blank' })

        expect(shouldHandleLinkWithNavigate(link)).toBe(false)
    })

    it('does not handle download links', () => {
        let link = makeLink({ href: '/report.csv', download: true })

        expect(shouldHandleLinkWithNavigate(link)).toBe(false)
    })

    it('does not handle non-http protocols', () => {
        expect(shouldHandleLinkWithNavigate(makeLink({ href: 'mailto:test@example.com' }))).toBe(false)
        expect(shouldHandleLinkWithNavigate(makeLink({ href: 'tel:+123456789' }))).toBe(false)
        expect(shouldHandleLinkWithNavigate(makeLink({ href: 'javascript:void(0)' }))).toBe(false)
    })
})
