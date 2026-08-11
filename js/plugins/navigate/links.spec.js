import { describe, it, expect } from 'vitest'
import { createUrlObjectFromString, isSameOrigin, linkShouldBeHandledNatively } from './links'

function link(html) {
    let el = document.createElement('div')

    el.innerHTML = html

    return el.firstElementChild
}

describe('isSameOrigin', () => {
    it('is true for a destination on the current origin', () => {
        expect(isSameOrigin(createUrlObjectFromString('/foo'))).toBe(true)
        expect(isSameOrigin(createUrlObjectFromString(window.location.origin + '/foo'))).toBe(true)
    })

    it('is false for a destination on another origin', () => {
        expect(isSameOrigin(createUrlObjectFromString('https://example.com/foo'))).toBe(false)
    })

    it('is false for a different port on the same host', () => {
        let { protocol, hostname } = window.location

        expect(isSameOrigin(createUrlObjectFromString(`${protocol}//${hostname}:9999/foo`))).toBe(false)
    })

    it('is false when there is no destination', () => {
        expect(isSameOrigin(createUrlObjectFromString(null))).toBe(false)
    })
})

describe('linkShouldBeHandledNatively', () => {
    it('handles same-origin links itself', () => {
        expect(linkShouldBeHandledNatively(link('<a href="/foo"></a>'))).toBe(false)
    })

    it('hands off links to another origin', () => {
        expect(linkShouldBeHandledNatively(link('<a href="https://example.com/foo"></a>'))).toBe(true)
    })

    it('hands off non-http protocols', () => {
        expect(linkShouldBeHandledNatively(link('<a href="mailto:foo@example.com"></a>'))).toBe(true)
    })

    it('hands off downloads and links with a target', () => {
        expect(linkShouldBeHandledNatively(link('<a href="/foo" download></a>'))).toBe(true)
        expect(linkShouldBeHandledNatively(link('<a href="/foo" target="_blank"></a>'))).toBe(true)
    })
})
