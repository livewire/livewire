import { describe, it, expect, vi, afterEach } from 'vitest'
import { prefetchHtml, storeThePrefetchedHtmlForWhenALinkIsClicked, getPretchedHtmlOr } from './prefetch'
import { createUrlObjectFromString } from './links'

let fetches = []

vi.mock('@/plugins/navigate/fetch', () => ({
    performFetch: (uri, callback) => fetches.push(callback),
}))

vi.mock('./history', () => ({
    storeCurrentPageStatus: () => {},
}))

describe('prefetchHtml', () => {
    afterEach(() => {
        vi.useRealTimers()
    })

    it('keeps a newer prefetch when the expiry timer of an older one fires', () => {
        vi.useFakeTimers()

        let destination = createUrlObjectFromString('/foo')
        let prefetch = () => prefetchHtml(destination, html => storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, destination), () => {})

        prefetch()
        fetches[0]('first')

        getPretchedHtmlOr(destination, () => {}, () => {})

        vi.advanceTimersByTime(29000)

        prefetch()

        vi.advanceTimersByTime(1000)

        expect(() => fetches[1]('second')).not.toThrow()

        let html

        getPretchedHtmlOr(destination, received => html = received, () => {})

        expect(html).toBe('second')
    })
})
