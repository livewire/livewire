import { afterEach, describe, expect, it, vi } from 'vitest'
import { hashHtml } from './htmlDelta'
import {
    applyChunkOps,
    applyFragmentOps,
    buildBlockManifest,
    buildFragmentManifest,
    createGzipBody,
    materializeRender,
    materializeSnapshotDelta,
    parseTransportFragments,
} from './renderTransport'

afterEach(() => {
    vi.unstubAllGlobals()
})

describe('render transport block manifests', () => {
    it('packs PHP-compatible rsync weak and CRC32 checksums', () => {
        expect(buildBlockManifest('abc')).toEqual({
            blockSize: 2048,
            blocks: 'AkoBJjUkQcI=',
        })
    })

    it('validates the fixed block size', () => {
        expect(() => buildBlockManifest('abc', 32))
            .toThrow('Invalid Livewire render block size')

        expect(() => buildBlockManifest('abc', 65537))
            .toThrow('Invalid Livewire render block size')
    })
})

describe('render transport chunk recipes', () => {
    it('reconstructs exact UTF-8 bytes using copy and add operations', () => {
        let baseline = 'α--middle--ω'
        let prefix = 'α--'
        let suffix = '--ω'
        let target = prefix + 'д字 🚀' + suffix
        let ops = [
            ['c', 0, byteLength(prefix)],
            ['a', encodeBase64('д字 🚀')],
            ['c', byteLength(prefix + 'middle'), byteLength(suffix)],
        ]

        expect(applyChunkOps(baseline, ops, byteLength(target))).toBe(target)
    })

    it('rejects malformed operations, ranges, base64, and UTF-8', () => {
        expect(() => applyChunkOps('abc', [['c', 2, 2]]))
            .toThrow('Invalid Livewire render chunk copy range')

        expect(() => applyChunkOps('abc', [['c', 0, 0]]))
            .toThrow('Invalid Livewire render chunk copy range')

        expect(() => applyChunkOps('abc', [['a', 'YQ']]))
            .toThrow('Invalid base64')

        expect(() => applyChunkOps('abc', [['a', '/w==']]))
            .toThrow('Invalid UTF-8')

        expect(() => applyChunkOps('abc', [['unknown', 0, 1]]))
            .toThrow('Invalid Livewire render chunk operation')
    })
})

describe('render transport fragments', () => {
    it('parses nested transport markers and records UTF-8 byte ranges', () => {
        let outer = 'a'.repeat(64)
        let inner = 'b'.repeat(64)
        let html = '<main>🚀' + fragment(outer, 'before' + fragment(inner, 'д字') + 'after') + '</main>'
        let fragments = parseTransportFragments(html)

        expect(fragments).toHaveLength(2)
        expect(fragments[0].token).toBe(outer)
        expect(fragments[0].parentIndex).toBeNull()
        expect(fragments[1].token).toBe(inner)
        expect(fragments[1].parentIndex).toBe(0)
        expect(fragments[1].depth).toBe(1)

        let source = new TextEncoder().encode(html)
        let content = new TextDecoder().decode(
            source.subarray(fragments[1].contentStart, fragments[1].contentEnd),
        )

        expect(content).toBe('д字')
    })

    it('builds content and skeleton digests that isolate nested changes', () => {
        let outer = 'a'.repeat(64)
        let inner = 'b'.repeat(64)
        let beforeHtml = '<main>' + fragment(
            outer,
            'before' + fragment(inner, 'one') + 'after',
        ) + '</main>'
        let afterHtml = '<main>' + fragment(
            outer,
            'before' + fragment(inner, 'two') + 'after',
        ) + '</main>'
        let before = buildFragmentManifest(beforeHtml)
        let after = buildFragmentManifest(afterHtml)

        expect(before.root).toMatch(/^[a-f0-9]{16}$/)
        expect(before.root).toBe(after.root)
        expect(before.nodes[0][1]).not.toBe(after.nodes[0][1])
        expect(before.nodes[0][2]).toBe(after.nodes[0][2])
        expect(before.nodes[1][1]).not.toBe(after.nodes[1][1])
        expect(before.nodes[1][2]).not.toBe(after.nodes[1][2])

        let leaf = buildFragmentManifest(fragment(inner, 'abc'))

        expect(leaf.nodes[0][1]).toBe('352441c2024d0127')
    })

    it('applies non-overlapping inner HTML replacements atomically', () => {
        let outer = 'a'.repeat(64)
        let inner = 'b'.repeat(64)
        let baseline = '<main>' + fragment(
            outer,
            'before' + fragment(inner, 'д字 👋') + 'after',
        ) + '</main>'
        let target = '<main>' + fragment(
            outer,
            'before' + fragment(inner, 'ж世 🚀') + 'after',
        ) + '</main>'

        expect(applyFragmentOps(
            baseline,
            [[inner, 'ж世 🚀']],
            byteLength(target),
        )).toBe(target)
    })

    it('rejects nested operations and malformed marker stacks', () => {
        let outer = 'a'.repeat(64)
        let inner = 'b'.repeat(64)
        let baseline = fragment(outer, fragment(inner, 'value'))

        expect(() => applyFragmentOps(baseline, [
            [outer, 'outer'],
            [inner, 'inner'],
        ])).toThrow('Overlapping Livewire render fragment operations')

        expect(() => parseTransportFragments(
            startFragment(outer) + 'value' + endFragment(inner),
        )).toThrow('Mismatched Livewire fragment markers')

        expect(() => parseTransportFragments(
            startFragment(outer) + 'value',
        )).toThrow('Unclosed Livewire fragment marker')
    })
})

describe('render descriptor materialization', () => {
    it('verifies full HTML supplied separately from the descriptor', async () => {
        let html = '<div>д字 ж世 з本 🚀</div>'
        let descriptor = {
            v: 1,
            mode: 'full',
            target: await hashHtml(html),
            bytes: byteLength(html),
            stats: {
                full: byteLength(html),
                selected: byteLength(html),
            },
        }

        await expect(materializeRender(descriptor, html)).resolves.toBe(html)
        await expect(materializeRender(descriptor, '<div>tampered</div>'))
            .rejects.toThrow('byte length verification failed')
    })

    it('returns the immutable message baseline for same renders', async () => {
        let html = '<div>unchanged</div>'
        let baseline = await makeBaseline(html)
        let descriptor = Object.freeze({
            v: 1,
            mode: 'same',
            base: baseline.hash,
            target: baseline.hash,
            bytes: baseline.bytes,
        })

        await expect(materializeRender(descriptor, null, baseline))
            .resolves.toBe(html)
        expect(Object.isFrozen(baseline)).toBe(true)
        expect(baseline.html).toBe(html)
    })

    it('materializes splice, chunk, and fragment modes', async () => {
        let spliceBaseline = '<div>д字 👋</div>'
        let spliceTarget = '<div>ж世 🚀</div>'
        let spliceState = await makeBaseline(spliceBaseline)
        let spliceDescriptor = {
            v: 1,
            mode: 'splice',
            base: spliceState.hash,
            target: await hashHtml(spliceTarget),
            bytes: byteLength(spliceTarget),
            patches: [{
                start: byteLength('<div>'),
                delete: byteLength('д字 👋'),
                insert: encodeBase64('ж世 🚀'),
            }],
        }

        await expect(materializeRender(spliceDescriptor, null, spliceState))
            .resolves.toBe(spliceTarget)

        let chunkBaseline = 'left-old-right'
        let chunkTarget = 'left-new-right'
        let chunkState = await makeBaseline(chunkBaseline)
        let chunkDescriptor = {
            v: 1,
            mode: 'chunks',
            base: chunkState.hash,
            target: await hashHtml(chunkTarget),
            bytes: byteLength(chunkTarget),
            ops: [
                ['c', 0, byteLength('left-')],
                ['a', encodeBase64('new')],
                ['c', byteLength('left-old'), byteLength('-right')],
            ],
        }

        await expect(materializeRender(chunkDescriptor, null, chunkState))
            .resolves.toBe(chunkTarget)

        let token = 'c'.repeat(64)
        let fragmentBaseline = '<div>' + fragment(token, 'old') + '</div>'
        let fragmentTarget = '<div>' + fragment(token, 'з本') + '</div>'
        let fragmentState = await makeBaseline(fragmentBaseline)
        let fragmentDescriptor = {
            v: 1,
            mode: 'fragments',
            base: fragmentState.hash,
            target: await hashHtml(fragmentTarget),
            bytes: byteLength(fragmentTarget),
            ops: [[token, 'з本']],
        }

        await expect(materializeRender(fragmentDescriptor, null, fragmentState))
            .resolves.toBe(fragmentTarget)
    })

    it('rejects invalid modes, stale baselines, malformed patches, and tampering', async () => {
        let baseline = await makeBaseline('<div>one</div>')
        let target = '<div>two</div>'
        let targetHash = await hashHtml(target)

        await expect(materializeRender({
            v: 1,
            mode: 'unknown',
            target: targetHash,
            bytes: byteLength(target),
        }, null, baseline)).rejects.toThrow('Invalid Livewire render descriptor')

        await expect(materializeRender({
            v: 1,
            mode: 'same',
            base: '0'.repeat(64),
            target: '0'.repeat(64),
            bytes: baseline.bytes,
        }, null, baseline)).rejects.toThrow('baseline does not match')

        await expect(materializeRender({
            v: 1,
            mode: 'splice',
            base: baseline.hash,
            target: targetHash,
            bytes: byteLength(target),
            patches: [{
                start: 5,
                delete: 3,
                insert: 'dHdv=',
            }],
        }, null, baseline)).rejects.toThrow('Invalid base64')

        await expect(materializeRender({
            v: 1,
            mode: 'full',
            target: targetHash,
            bytes: byteLength(target),
        }, '<div>six</div>')).rejects.toThrow('integrity verification failed')

        await expect(materializeRender({
            v: 1,
            mode: 'full',
            target: targetHash,
            bytes: byteLength(target),
            requestGzip: 0,
        }, target)).rejects.toThrow('Invalid Livewire request compression threshold')
    })
})

describe('snapshot deltas', () => {
    it('reconstructs and verifies a Unicode snapshot using byte patches', async () => {
        let baseline = '{"memo":"д字 👋","count":1}'
        let target = '{"memo":"ж世 🚀","count":2}'
        let patches = [
            {
                start: byteLength('{"memo":"'),
                delete: byteLength('д字 👋'),
                insert: encodeBase64('ж世 🚀'),
            },
            {
                start: byteLength('{"memo":"д字 👋","count":'),
                delete: 1,
                insert: encodeBase64('2'),
            },
        ]
        let delta = {
            v: 1,
            base: await hashHtml(baseline),
            target: await hashHtml(target),
            bytes: byteLength(target),
            patches,
        }

        await expect(materializeSnapshotDelta(delta, baseline)).resolves.toBe(target)
    })

    it('rejects snapshot tampering and accepts an empty same-snapshot patch list', async () => {
        let baseline = '{"count":1}'
        let hash = await hashHtml(baseline)
        let same = {
            v: 1,
            base: hash,
            target: hash,
            bytes: byteLength(baseline),
            patches: [],
        }

        await expect(materializeSnapshotDelta(same, baseline)).resolves.toBe(baseline)

        await expect(materializeSnapshotDelta({
            ...same,
            target: '0'.repeat(64),
        }, baseline)).rejects.toThrow('integrity verification failed')
    })
})

describe('gzip request bodies', () => {
    it('falls back to null when CompressionStream is unavailable', async () => {
        vi.stubGlobal('CompressionStream', undefined)

        await expect(createGzipBody('payload')).resolves.toBeNull()
    })

    it('falls back to null when compression fails', async () => {
        vi.stubGlobal('CompressionStream', function () {
            throw new Error('compression failed')
        })

        await expect(createGzipBody('payload')).resolves.toBeNull()
    })

    it('creates a valid gzip stream when the platform supports it', async () => {
        if (typeof globalThis.CompressionStream !== 'function'
            || typeof globalThis.DecompressionStream !== 'function'
        ) {
            return
        }

        let value = 'Livewire 🚀 '.repeat(100)
        let compressed = await createGzipBody(value)
        let decompression = new DecompressionStream('gzip')
        let output = new Response(decompression.readable).text()
        let writer = decompression.writable.getWriter()

        await writer.write(compressed)
        await writer.close()

        let decompressed = await output

        expect(compressed).toBeInstanceOf(Uint8Array)
        expect(decompressed).toBe(value)
    })
})

async function makeBaseline(html) {
    return Object.freeze({
        html,
        hash: await hashHtml(html),
        bytes: byteLength(html),
        revision: 1,
        chunks: null,
        fragments: null,
    })
}

function fragment(token, content) {
    return startFragment(token) + content + endFragment(token)
}

function startFragment(token) {
    return '<!--[if FRAGMENT:type=transport|name=fragment|token='
        + token
        + ']><![endif]-->'
}

function endFragment(token) {
    return '<!--[if ENDFRAGMENT:type=transport|name=fragment|token='
        + token
        + ']><![endif]-->'
}

function byteLength(value) {
    return new TextEncoder().encode(value).length
}

function encodeBase64(value) {
    let bytes = new TextEncoder().encode(value)
    let binary = Array.from(bytes, byte => String.fromCharCode(byte)).join('')

    return btoa(binary)
}
