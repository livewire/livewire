import { afterEach, describe, expect, it, vi } from 'vitest'
import { hashHtml } from '../htmlDelta'
import { fireAction, interceptMessage, interceptRequest } from './index'

afterEach(() => {
    vi.restoreAllMocks()
    vi.unstubAllGlobals()
    document.head.innerHTML = ''
    document.body.innerHTML = ''
    delete window.livewireScriptConfig
})

describe('Request System', () => {
    it('retries a missing snapshot reference once with the full snapshot', async () => {
        document.head.innerHTML = '<meta name="csrf-token" content="token">'
        window.livewireScriptConfig = { uri: '/livewire/update' }
        vi.stubGlobal('Alpine', {
            transaction: async callback => await callback(),
        })
        vi.stubGlobal('requestAnimationFrame', callback => callback())

        let component = createComponent()
        component.outboundSnapshotReference = 'a'.repeat(24)
        component.outboundSnapshotReferenceSnapshot = component.initialSnapshot
        let requests = []
        let responseSnapshot = JSON.stringify({
            data: { value: 'updated' },
            memo: component.snapshot.memo,
        })

        vi.stubGlobal('fetch', vi.fn(async (uri, options) => {
            requests.push({
                body: options.body instanceof Uint8Array
                    ? options.body.slice()
                    : options.body,
                headers: { ...options.headers },
                uri,
            })

            if (requests.length === 1) {
                return response(409, {
                    snapshotMissing: [component.id],
                }, {
                    'X-Livewire-Snapshot-Missing': '1',
                })
            }

            return response(200, {
                components: [{
                    id: component.id,
                    snapshot: responseSnapshot,
                    snapshotRef: 'b'.repeat(24),
                    effects: { returns: [null] },
                }],
                assets: [],
            })
        }))

        await fireAction(component, '$refresh')

        expect(requests).toHaveLength(2)

        let firstPayload = JSON.parse(requests[0].body)

        expect(firstPayload.components[0].snapshot).toBeUndefined()
        expect(firstPayload.components[0].snapshotRef).toBe('a'.repeat(24))
        expect(component.rejectedSnapshotReference).toBe('a'.repeat(24))

        let secondBody = await decodeRequestBody(requests[1])
        let secondPayload = JSON.parse(secondBody)

        expect(secondPayload.components[0].snapshot).toBe(component.initialSnapshot)
        expect(secondPayload.components[0].snapshotRef).toBeUndefined()
        expect(requests[1].headers['X-CSRF-TOKEN']).toBe('token')
        expect(component.rememberedSnapshotReference).toBe('b'.repeat(24))
    })

    it('does not use a snapshot reference when its exact full fallback exceeds the server limit', async () => {
        document.head.innerHTML = '<meta name="csrf-token" content="token">'
        window.livewireScriptConfig = { uri: '/livewire/update' }
        vi.stubGlobal('Alpine', {
            transaction: async callback => await callback(),
        })
        vi.stubGlobal('requestAnimationFrame', callback => callback())

        let component = createComponent()
        component.outboundSnapshotReference = 'a'.repeat(24)
        component.outboundSnapshotReferenceSnapshot = component.initialSnapshot
        component.maximumRequestBytes = 100
        let requests = []

        vi.stubGlobal('fetch', vi.fn(async (uri, options) => {
            requests.push({ body: options.body, uri })

            return response(200, {
                components: [{
                    id: component.id,
                    snapshot: component.initialSnapshot,
                    effects: { returns: [null] },
                }],
                assets: [],
            })
        }))

        await fireAction(component, '$refresh')

        expect(requests).toHaveLength(1)

        let payload = JSON.parse(requests[0].body)

        expect(payload.components[0].snapshot).toBe(component.initialSnapshot)
        expect(payload.components[0].snapshotRef).toBeUndefined()
    })

    it('retries a related bundled miss with full snapshots for every referenced message', async () => {
        document.head.innerHTML = '<meta name="csrf-token" content="token">'
        window.livewireScriptConfig = { uri: '/livewire/update' }
        vi.stubGlobal('Alpine', {
            transaction: async callback => await callback(),
        })
        vi.stubGlobal('requestAnimationFrame', callback => callback())

        let missing = createComponent('missing-component')
        let valid = createComponent('valid-component')
        let requests = []

        for (let component of [missing, valid]) {
            component.outboundSnapshotReference = component.id[0].repeat(24)
            component.outboundSnapshotReferenceSnapshot = component.initialSnapshot
        }

        vi.stubGlobal('fetch', vi.fn(async (uri, options) => {
            requests.push({ body: options.body, uri })

            if (requests.length === 1) {
                return response(409, {
                    snapshotMissing: [missing.id],
                }, {
                    'X-Livewire-Snapshot-Missing': '1',
                })
            }

            return response(200, {
                components: [missing, valid].map(component => ({
                    id: component.id,
                    snapshot: component.initialSnapshot,
                    effects: { returns: [null] },
                })),
                assets: [],
            })
        }))

        await Promise.all([
            fireAction(missing, '$refresh'),
            fireAction(valid, '$refresh'),
        ])

        expect(requests).toHaveLength(2)

        let retry = JSON.parse(requests[1].body).components
        let missingRetry = retry.find(component => {
            return JSON.parse(component.snapshot).memo.id === missing.id
        })
        let validRetry = retry.find(component => {
            return JSON.parse(component.snapshot).memo.id === valid.id
        })

        expect(missingRetry.snapshot).toBe(missing.initialSnapshot)
        expect(missingRetry.snapshotRef).toBeUndefined()
        expect(validRetry.snapshot).toBe(valid.initialSnapshot)
        expect(validRetry.snapshotRef).toBeUndefined()
    })

    it('does not retry a snapshot missing response unrelated to the references it sent', async () => {
        document.head.innerHTML = '<meta name="csrf-token" content="token">'
        window.livewireScriptConfig = { uri: '/livewire/update' }
        vi.stubGlobal('Alpine', {
            transaction: async callback => await callback(),
        })
        vi.stubGlobal('requestAnimationFrame', callback => callback())

        let component = createComponent()
        component.outboundSnapshotReference = 'a'.repeat(24)
        component.outboundSnapshotReferenceSnapshot = component.initialSnapshot
        let requests = []
        let removeInterceptor = interceptRequest(({ onError }) => {
            onError(({ preventDefault }) => preventDefault())
        })

        vi.stubGlobal('fetch', vi.fn(async (uri, options) => {
            requests.push({ body: options.body, uri })

            return response(409, {
                snapshotMissing: ['unrelated-component'],
            }, {
                'X-Livewire-Snapshot-Missing': '1',
            })
        }))

        await expect(fireAction(component, '$refresh')).rejects.toBeTruthy()
        removeInterceptor()

        expect(requests).toHaveLength(1)
    })

    it('materializes snapshot and render deltas before public message hooks', async () => {
        document.head.innerHTML = '<meta name="csrf-token" content="token">'
        window.livewireScriptConfig = { uri: '/livewire/update' }
        vi.stubGlobal('Alpine', {
            transaction: async callback => await callback(),
        })
        vi.stubGlobal('requestAnimationFrame', callback => callback())

        let component = createComponent()
        let html = '<div>materialized</div>'
        let snapshotHash = await hashHtml(component.initialSnapshot)
        let observedPayload = null
        let removeInterceptor = interceptMessage(({ message, onSuccess }) => {
            if (message.component !== component) return

            onSuccess(({ payload }) => observedPayload = payload)
        })

        vi.stubGlobal('fetch', vi.fn(async () => response(200, {
            components: [{
                id: component.id,
                snapshotDelta: {
                    v: 1,
                    base: snapshotHash,
                    target: snapshotHash,
                    bytes: new TextEncoder().encode(component.initialSnapshot).length,
                    patches: [],
                },
                effects: {
                    render: {
                        v: 1,
                        mode: 'full',
                        target: await hashHtml(html),
                        bytes: new TextEncoder().encode(html).length,
                    },
                    html,
                    returns: [null],
                },
            }],
            assets: [],
        })))

        await fireAction(component, '$refresh')
        removeInterceptor()

        expect(observedPayload.snapshot).toEqual(JSON.parse(component.initialSnapshot))
        expect(observedPayload.effects.html).toBe(html)
        expect(component.processedEffects.html).toBe(html)
    })

    it('compresses requests only after the server negotiates gzip support', async () => {
        if (typeof globalThis.CompressionStream !== 'function'
            || typeof globalThis.DecompressionStream !== 'function'
        ) {
            return
        }

        document.head.innerHTML = '<meta name="csrf-token" content="token">'
        window.livewireScriptConfig = { uri: '/livewire/update' }
        vi.stubGlobal('Alpine', {
            transaction: async callback => await callback(),
        })
        vi.stubGlobal('requestAnimationFrame', callback => callback())

        let component = createComponent()
        let requests = []
        let html = '<div>gzip negotiation</div>'

        vi.stubGlobal('fetch', vi.fn(async (uri, options) => {
            requests.push({
                body: options.body instanceof Uint8Array
                    ? options.body.slice()
                    : options.body,
                headers: { ...options.headers },
                uri,
            })

            let snapshot = JSON.stringify({
                data: { value: 'updated-' + requests.length },
                memo: component.snapshot.memo,
            })

            if (requests.length === 1) {
                return response(200, {
                    components: [{
                        id: component.id,
                        snapshot,
                        effects: {
                            html,
                            render: {
                                v: 1,
                                mode: 'full',
                                target: await hashHtml(html),
                                bytes: new TextEncoder().encode(html).length,
                                requestGzip: 1,
                            },
                            returns: [null],
                        },
                    }],
                    assets: [],
                })
            }

            return response(200, {
                components: [{
                    id: component.id,
                    snapshot,
                    effects: { returns: [null] },
                }],
                assets: [],
            })
        }))

        await fireAction(component, '$refresh')
        await fireAction(component, '$refresh')

        expect(requests).toHaveLength(2)
        expect(requests[0].body).toEqual(expect.any(String))
        expect(requests[0].headers['Content-Encoding']).toBeUndefined()
        expect(requests[1].body).toBeInstanceOf(Uint8Array)
        expect(requests[1].headers['Content-Encoding']).toBe('gzip')

        let secondPayload = JSON.parse(await decodeRequestBody(requests[1]))

        expect(JSON.parse(secondPayload.components[0].snapshot).data.value)
            .toBe('updated-1')
    })

    it('updates and clears gzip negotiation from top-level transport on renderless responses', async () => {
        if (typeof globalThis.CompressionStream !== 'function'
            || typeof globalThis.DecompressionStream !== 'function'
        ) {
            return
        }

        document.head.innerHTML = '<meta name="csrf-token" content="token">'
        window.livewireScriptConfig = { uri: '/livewire/update' }
        vi.stubGlobal('Alpine', {
            transaction: async callback => await callback(),
        })
        vi.stubGlobal('requestAnimationFrame', callback => callback())

        let component = createComponent()
        let requests = []

        vi.stubGlobal('fetch', vi.fn(async (uri, options) => {
            requests.push({
                body: options.body instanceof Uint8Array
                    ? options.body.slice()
                    : options.body,
                headers: { ...options.headers },
                uri,
            })

            let snapshot = JSON.stringify({
                data: { value: 'updated-' + requests.length },
                memo: component.snapshot.memo,
            })

            return response(200, {
                transport: {
                    v: 1,
                    requestGzip: requests.length === 1 ? 1 : null,
                },
                components: [{
                    id: component.id,
                    snapshot,
                    effects: { returns: [null] },
                }],
                assets: [],
            })
        }))

        await fireAction(component, '$refresh')
        await fireAction(component, '$refresh')
        await fireAction(component, '$refresh')

        expect(requests).toHaveLength(3)
        expect(requests[0].body).toEqual(expect.any(String))
        expect(requests[0].headers['Content-Encoding']).toBeUndefined()
        expect(requests[1].body).toBeInstanceOf(Uint8Array)
        expect(requests[1].headers['Content-Encoding']).toBe('gzip')
        expect(requests[2].body).toEqual(expect.any(String))
        expect(requests[2].headers['Content-Encoding']).toBeUndefined()
        expect(component.requestCompressionMinimumBytes).toBeNull()
    })
})

function createComponent(id = 'component-id') {
    let snapshot = JSON.stringify({
        data: { value: 'x'.repeat(5000) },
        memo: {
            async: [],
            children: {},
            id,
            islands: {},
            name: 'transport-test',
        },
    })

    return {
        id,
        effects: {},
        initialSnapshot: snapshot,
        snapshotEncoded: snapshot,
        snapshot: JSON.parse(snapshot),
        islands: {},
        isIsolated: false,
        isLazy: false,
        hasBeenLazyLoaded: true,
        isLazyIsolated: false,

        captureRenderBaseline() {
            return null
        },

        captureSnapshotReference(allowed, snapshot) {
            return allowed && snapshot === this.outboundSnapshotReferenceSnapshot
                ? this.outboundSnapshotReference || null
                : null
        },

        getDeepChildrenWithBindings() {},

        getEncodedSnapshotWithLatestChildrenMergedIn() {
            return this.snapshotEncoded
        },

        getRenderMetadata() {
            return {
                v: 1,
                capabilities: ['snapshot-delta', 'snapshot-ref'],
            }
        },

        getUpdates() {
            return {}
        },

        mergeNewSnapshot(snapshotEncoded, effects) {
            this.snapshotEncoded = snapshotEncoded
            this.snapshot = JSON.parse(snapshotEncoded)
            this.effects = effects
        },

        processEffects(effects) {
            this.processedEffects = effects
        },

        rejectSnapshotReference(reference) {
            this.rejectedSnapshotReference = reference
        },

        rememberSnapshotReference(reference, snapshot) {
            this.rememberedSnapshotReference = reference
            this.rememberedSnapshotReferenceSnapshot = snapshot
        },

        rememberRequestCompression(minimumBytes) {
            this.requestCompressionMinimumBytes = minimumBytes
        },

        getRequestCompressionMinimumBytes() {
            return this.requestCompressionMinimumBytes ?? null
        },

        getMaximumRequestBytes() {
            return this.maximumRequestBytes ?? 1024 * 1024
        },
    }
}

function response(status, payload, headers = {}) {
    return {
        aborted: false,
        headers: new Headers(headers),
        ok: status >= 200 && status < 300,
        redirected: false,
        status,
        text: async () => JSON.stringify(payload),
    }
}

async function decodeRequestBody(request) {
    if (request.headers['Content-Encoding'] !== 'gzip') return request.body

    let decompression = new DecompressionStream('gzip')
    let output = new Response(decompression.readable).text()
    let writer = decompression.writable.getWriter()

    await writer.write(request.body)
    await writer.close()

    return await output
}
