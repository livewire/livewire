import { beforeEach, describe, expect, it } from 'vitest'
import { track } from './index'

describe('History query key decoding', () => {
    beforeEach(() => {
        window.history.replaceState({}, '', '/livewire-test')
    })

    it('reads encoded non-array query keys as their decoded key names', async () => {
        window.history.replaceState({}, '', '/livewire-test?foo%20bar=baz')

        let tracked = track('foo bar', 'seed')

        await new Promise(queueMicrotask)

        expect(tracked.initial).toBe('baz')
    })

    it('updates an encoded key without creating duplicate query params', async () => {
        window.history.replaceState({}, '', '/livewire-test?foo%20bar=baz')

        let tracked = track('foo bar', '')
        tracked.replace('qux')

        await new Promise(queueMicrotask)

        expect(window.location.search).toBe('?foo%20bar=qux')
    })
})
