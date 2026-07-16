import { describe, it, expect } from 'vitest'
import { kindFromMime, kindFromName } from './kind'
import { TemporaryUpload } from './synth'

describe('kindFromMime', () => {
    it('buckets by the MIME top-level type', () => {
        expect(kindFromMime('image/png')).toBe('image')
        expect(kindFromMime('image/svg+xml')).toBe('image')
        expect(kindFromMime('audio/mpeg')).toBe('audio')
        expect(kindFromMime('video/mp4')).toBe('video')
    })

    it('falls back to "file" for everything else', () => {
        expect(kindFromMime('application/pdf')).toBe('file')
        expect(kindFromMime('text/plain')).toBe('file')
        expect(kindFromMime('')).toBe('file')
        expect(kindFromMime(null)).toBe('file')
    })
})

describe('kindFromName', () => {
    it('buckets by filename extension, case-insensitively', () => {
        expect(kindFromName('photo.png')).toBe('image')
        expect(kindFromName('photo.JPG')).toBe('image')
        expect(kindFromName('song.mp3')).toBe('audio')
        expect(kindFromName('clip.mov')).toBe('video')
    })

    it('falls back to "file" for everything else', () => {
        expect(kindFromName('report.pdf')).toBe('file')
        expect(kindFromName('archive.zip')).toBe('file')
        expect(kindFromName('no-extension')).toBe('file')
        expect(kindFromName('')).toBe('file')
        expect(kindFromName(null)).toBe('file')
    })
})

describe('TemporaryUpload kind', () => {
    it('trusts the native MIME type while the file is still around', () => {
        // A pending upload wraps the browser's File object — its declared
        // type wins even when the filename extension disagrees...
        let pending = TemporaryUpload.pending({ name: 'disguised.txt', type: 'image/png' })

        expect(pending.kind).toBe('image')
        expect(pending.isImage).toBe(true)
        expect(pending.isAudio).toBe(false)
        expect(pending.isVideo).toBe(false)
    })

    it('falls back to the extension when the browser reports no MIME type', () => {
        let pending = TemporaryUpload.pending({ name: 'photo.png', type: '' })

        expect(pending.kind).toBe('image')
    })

    it('derives kind from the original filename after the upload finishes', () => {
        let finished = new TemporaryUpload('livewire-file:abc123.png', { name: 'song.mp3' })

        expect(finished.kind).toBe('audio')
        expect(finished.isAudio).toBe(true)
        expect(finished.isImage).toBe(false)
    })
})
