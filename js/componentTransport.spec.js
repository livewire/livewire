import { describe, expect, it } from 'vitest'
import { Component } from './component'
import { hashHtml } from './htmlDelta'

describe('component render transport state', () => {
    it('does not advertise transport without a delta handshake or legacy hash', () => {
        let component = transportComponent(false)

        expect(component.getRenderMetadata()).toEqual({})
    })

    it('keeps the legacy htmlHash protocol during a rolling deployment', async () => {
        let component = transportComponent(false)
        let html = '<div>legacy baseline</div>'
        let hash = await hashHtml(html)

        component.rememberServerRenderedHtml(html, hash)

        expect(component.getRenderMetadata()).toEqual({ htmlHash: hash })
    })

    it('advertises v1 without treating the parsed mount DOM as a baseline', () => {
        let component = transportComponent()
        let metadata = component.getRenderMetadata()

        expect(metadata.v).toBe(1)
        expect(metadata.base).toBeUndefined()
        expect(metadata.capabilities).toContain('snapshot-delta')
        expect(metadata.capabilities).toContain('snapshot-ref')
    })

    it('captures an immutable exact baseline and emits portable manifests', async () => {
        let component = transportComponent()
        let token = 'a'.repeat(16)
        let marker = '<!--[if FRAGMENT:type=transport|name=row|token=' + token + ']><![endif]-->'
        let end = '<!--[if ENDFRAGMENT:type=transport|name=row|token=' + token + ']><![endif]-->'
        let html = '<div>' + marker + 'value'.repeat(2000) + end + '</div>'
        let hash = await hashHtml(html)

        component.rememberServerRenderedHtml(html, hash)

        let baseline = component.captureRenderBaseline()
        let metadata = component.getRenderMetadata(baseline)

        expect(Object.isFrozen(baseline)).toBe(true)
        expect(metadata.base).toEqual({
            hash,
            bytes: new TextEncoder().encode(html).length,
            revision: 1,
        })
        expect(metadata.capabilities).toContain('chunks')
        expect(metadata.capabilities).toContain('fragments')
        expect(metadata.chunks.blocks).not.toBe('')
        expect(metadata.fragments.nodes).toHaveLength(1)
    })

    it('does not expose a baseline after its legacy integrity aliases are changed', async () => {
        let component = transportComponent()
        let html = '<div>baseline</div>'

        component.rememberServerRenderedHtml(html, await hashHtml(html))
        component.serverRenderedHtmlHash = '0'.repeat(64)

        expect(component.captureRenderBaseline()).toBeNull()
    })

    it('temporarily stops sending portable manifests after repeated full losses', async () => {
        let component = transportComponent()
        let html = '<div>' + 'large'.repeat(2000) + '</div>'
        let hash = await hashHtml(html)

        component.rememberServerRenderedHtml(html, hash)

        for (let index = 0; index < 3; index++) {
            let baseline = component.captureRenderBaseline()

            component.rememberServerRenderedHtml(
                html,
                hash,
                { v: 1, mode: 'full', target: hash },
                baseline,
                true,
            )
        }

        let coolingDown = component.getRenderMetadata(component.captureRenderBaseline())

        expect(coolingDown.capabilities).not.toContain('chunks')

        for (let index = 0; index < 4; index++) {
            component.getRenderMetadata(component.captureRenderBaseline())
        }

        let resumed = component.getRenderMetadata(component.captureRenderBaseline())

        expect(resumed.capabilities).toContain('chunks')
    })

    it('keeps oversized render and fragment manifests out of request metadata', async () => {
        let component = transportComponent()
        let oversized = '<div>' + 'x'.repeat(4 * 1024 * 1024) + '</div>'
        let oversizedHash = await hashHtml(oversized)

        component.rememberServerRenderedHtml(oversized, oversizedHash)

        let oversizedMetadata = component.getRenderMetadata(component.captureRenderBaseline())

        expect(oversizedMetadata.capabilities).not.toContain('splice')
        expect(oversizedMetadata.chunks).toBeUndefined()
        expect(oversizedMetadata.fragments).toBeUndefined()

        let fragments = ''

        for (let index = 0; index < 1025; index++) {
            let token = index.toString(16).padStart(16, '0')

            fragments += '<!--[if FRAGMENT:type=transport|name=row|token=' + token + ']><![endif]-->'
                + 'value'
                + '<!--[if ENDFRAGMENT:type=transport|name=row|token=' + token + ']><![endif]-->'
        }

        let fragmented = '<div>' + fragments + '</div>'

        component.rememberServerRenderedHtml(fragmented, await hashHtml(fragmented))

        let fragmentedMetadata = component.getRenderMetadata(component.captureRenderBaseline())

        expect(fragmentedMetadata.capabilities).toContain('chunks')
        expect(fragmentedMetadata.capabilities).not.toContain('fragments')
        expect(fragmentedMetadata.fragments).toBeUndefined()
    })

    it('only retains valid snapshot references outside the miss cooldown', () => {
        let component = transportComponent()
        let reference = 'a'.repeat(24)

        component.rememberSnapshotReference(reference, 'snapshot')
        expect(component.captureSnapshotReference(true, 'snapshot')).toBe(reference)
        expect(component.captureSnapshotReference(true, 'changed snapshot')).toBeNull()

        component.rejectSnapshotReference(reference)
        component.rememberSnapshotReference('b'.repeat(24), 'snapshot')

        expect(component.captureSnapshotReference(true, 'snapshot')).toBeNull()
        expect(component.snapshotReferenceCooldown).toBe(5)
    })

    it('persists the delta handshake in navigate-cached DOM', () => {
        let component = transportComponent()
        let attributes = {}

        component.el = {
            setAttribute(name, value) {
                attributes[name] = value
            },
        }
        component.snapshotEncoded = '{"memo":{}}'
        component.originalEffects = {}
        component.key = null

        component.inscribeSnapshotAndEffectsOnElement()

        expect(JSON.parse(attributes['wire:effects']).renderTransport)
            .toEqual(component.renderTransportConfig)
    })

    it('clears negotiated request compression when a v1 response disables it', () => {
        let component = transportComponent()

        component.rememberRequestCompression(1024)
        expect(component.getRequestCompressionMinimumBytes()).toBe(1024)

        component.rememberRequestCompression(undefined)
        expect(component.getRequestCompressionMinimumBytes()).toBeNull()
    })
})

function transportComponent(enabled = true) {
    let component = Object.create(Component.prototype)

    component.serverRenderedHtml = null
    component.serverRenderedHtmlHash = null
    component.renderBaseline = null
    component.renderRevision = 0
    component.transportFullLosses = 0
    component.transportManifestCooldown = 0
    component.snapshotReference = null
    component.snapshotReferenceSnapshot = null
    component.snapshotReferenceCooldown = 0
    component.requestCompressionMinimumBytes = null
    component.htmlResyncPending = false
    component.htmlResyncPromise = null
    component.renderTransportConfig = enabled
        ? Object.freeze({
            v: 1,
            minimumBytes: 8192,
            maximumBytes: 4 * 1024 * 1024,
            blockSize: 2048,
            maximumManifestBytes: 65536,
            maximumFragments: 1024,
            cacheAccelerator: true,
            snapshotDelta: true,
            snapshotReferences: true,
            maximumRequestBytes: 1024 * 1024,
        })
        : null

    return component
}
