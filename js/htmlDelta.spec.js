import { describe, expect, it } from 'vitest'
import { applyHtmlDelta, hashHtml, reconstructHtmlDelta } from './htmlDelta'

describe('HTML deltas', () => {
    it('applies a byte-range replacement', () => {
        expect(applyHtmlDelta('<div>1</div>', {
            start: 5,
            delete: 1,
            insert: encodeBase64('2'),
        })).toBe('<div>2</div>')
    })

    it('applies inserts and deletes', () => {
        expect(applyHtmlDelta('<div>old value</div>', {
            start: 5,
            delete: 9,
            insert: encodeBase64('new'),
        })).toBe('<div>new</div>')
    })

    it('uses UTF-8 byte offsets without corrupting unicode', () => {
        let prefix = '<div>'
        let from = `${prefix}д字 👋</div>`

        expect(applyHtmlDelta(from, {
            start: byteLength(prefix),
            delete: byteLength('д字 👋'),
            insert: encodeBase64('ж世 🚀'),
        })).toBe('<div>ж世 🚀</div>')
    })

    it('applies multiple patches using offsets from the original HTML', () => {
        let from = '<div><span>Todo card</span><main>Stable content</main><aside>Done</aside></div>'
        let removedStart = byteLength('<div>')
        let removedLength = byteLength('<span>Todo card</span>')
        let insertionStart = byteLength('<div><span>Todo card</span><main>Stable content</main><aside>Done')

        expect(applyHtmlDelta(from, [
            {
                start: removedStart,
                delete: removedLength,
                insert: '',
            },
            {
                start: insertionStart,
                delete: 0,
                insert: encodeBase64('<span>Todo card</span>'),
            },
        ])).toBe('<div><main>Stable content</main><aside>Done<span>Todo card</span></aside></div>')
    })

    it('accepts an empty patch list', () => {
        expect(applyHtmlDelta('<div>unchanged</div>', [])).toBe('<div>unchanged</div>')
    })

    it('rejects an out-of-bounds range', () => {
        expect(() => applyHtmlDelta('<div></div>', {
            start: 100,
            delete: 0,
            insert: '',
        })).toThrow('Invalid Livewire HTML delta range')
    })

    it('rejects overlapping patches', () => {
        expect(() => applyHtmlDelta('<div>content</div>', [
            { start: 5, delete: 3, insert: '' },
            { start: 6, delete: 1, insert: '' },
        ])).toThrow('Invalid Livewire HTML delta range')
    })

    it('hashes the exact UTF-8 bytes used by the server', async () => {
        expect(await hashHtml('<div>д字 🚀</div>'))
            .toBe('aec6c9575dda2ec914dead7a34c741d7cfd1c305d0808fa88e85e95a0909029f')
    })

    it('verifies reconstructed HTML before returning it for morphing', async () => {
        let from = '<div>Count: 1</div>'
        let to = '<div>Count: 2</div>'
        let patches = [{
            start: byteLength('<div>Count: '),
            delete: 1,
            insert: encodeBase64('2'),
        }]
        let expectedHash = await hashHtml(to)

        await expect(reconstructHtmlDelta(from, patches, expectedHash)).resolves.toBe(to)
    })

    it('rejects reconstructed HTML when a patch fails its integrity check', async () => {
        let from = '<div>Count: 1</div>'
        let expectedHash = await hashHtml('<div>Count: 2</div>')
        let tamperedPatches = [{
            start: byteLength('<div>Count: '),
            delete: 1,
            insert: encodeBase64('9'),
        }]

        await expect(reconstructHtmlDelta(from, tamperedPatches, expectedHash))
            .rejects.toThrow('Livewire HTML delta integrity check failed')
    })
})

function byteLength(value) {
    return new TextEncoder().encode(value).length
}

function encodeBase64(value) {
    let bytes = new TextEncoder().encode(value)
    let binary = Array.from(bytes, byte => String.fromCharCode(byte)).join('')

    return btoa(binary)
}
