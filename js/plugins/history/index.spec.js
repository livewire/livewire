import { beforeEach, describe, expect, it } from 'vitest'
import { track } from './index'

describe('History query string tracking', () => {
    beforeEach(() => {
        window.history.replaceState({}, '', '/livewire-test')
    })

    it('preserves existing query params without values when updating tracked params', async () => {
        window.history.replaceState({}, '', '/livewire-test?flag#section')

        let tracked = track('foo', '')

        tracked.replace('bar')

        await new Promise(queueMicrotask)

        expect(window.location.search).toBe('?flag=&foo=bar')
        expect(window.location.hash).toBe('#section')
    })

    it('treats query params without values as present initial values', async () => {
        window.history.replaceState({}, '', '/livewire-test?foo')

        let tracked = track('foo', 'seed')

        await new Promise(queueMicrotask)

        expect(tracked.initial).toBe('')
        expect(window.location.search).toBe('?foo')
    })
})
