import { describe, it, expect } from 'vitest'
import { TemporaryUpload } from './synth'

describe('TemporaryUpload filename', () => {
    it('extracts the server filename from a signed wire value', () => {
        let upload = new TemporaryUpload('livewire-file:deadbeef:abc123-photo.png')

        expect(upload.filename).toBe('abc123-photo.png')
    })

    it('continues to read legacy unsigned wire values', () => {
        let upload = new TemporaryUpload('livewire-file:abc123-photo.png')

        expect(upload.filename).toBe('abc123-photo.png')
    })
})
