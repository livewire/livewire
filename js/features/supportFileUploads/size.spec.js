import { describe, it, expect } from 'vitest'
import { sizeForHumans } from './size'
import { TemporaryUpload } from './synth'

describe('sizeForHumans', () => {
    it('formats byte counts into the nearest sensible unit', () => {
        expect(sizeForHumans(0)).toBe('0 B')
        expect(sizeForHumans(512)).toBe('512 B')
        expect(sizeForHumans(1024)).toBe('1 KB')
        expect(sizeForHumans(1536)).toBe('1.5 KB')
        expect(sizeForHumans(15 * 1024)).toBe('15 KB')
        expect(sizeForHumans(1024 * 1024)).toBe('1 MB')
        expect(sizeForHumans(2.5 * 1024 * 1024)).toBe('2.5 MB')
        expect(sizeForHumans(3.4 * 1024 ** 3)).toBe('3.4 GB')
    })

    it('rounds to at most one decimal place', () => {
        expect(sizeForHumans(1234567)).toBe('1.2 MB')
        expect(sizeForHumans(1024 * 1024 - 1)).toBe('1 MB')
    })

    it('promotes near-miss sizes to the next unit instead of showing "1010 KB"', () => {
        expect(sizeForHumans(1000)).toBe('1 KB')
        expect(sizeForHumans(1010 * 1024)).toBe('1 MB')
    })
})

describe('TemporaryUpload size', () => {
    it('reads bytes from the native File object while the upload is pending', () => {
        let pending = TemporaryUpload.pending({ name: 'photo.png', size: 1536 })

        expect(pending.size).toBe(1536)
        expect(pending.sizeForHumans).toBe('1.5 KB')
    })

    it('reads bytes from the server meta after hydration', () => {
        let hydrated = new TemporaryUpload('livewire-file:abc123.png', { size: 2.5 * 1024 * 1024 })

        expect(hydrated.size).toBe(2.5 * 1024 * 1024)
        expect(hydrated.sizeForHumans).toBe('2.5 MB')
    })

    it('returns null when the byte count is unknown', () => {
        let hydrated = new TemporaryUpload('livewire-file:abc123.png', {})

        expect(hydrated.size).toBe(null)
        expect(hydrated.sizeForHumans).toBe(null)
    })
})
