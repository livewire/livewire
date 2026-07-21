import { dataSet, deepClone, diff, diffAndConsolidate, diffAndPatchRecursive, extractData} from '@/utils'
import { dehydrateTree } from '@/synths'
import { generateWireObject } from '@/$wire'
import { findComponentByEl, findComponent, hasComponent } from '@/store'
import { trigger } from '@/hooks'
import { setNextActionOrigin } from '@/request'
import { isSha256Hash, supportsHtmlDeltaVerification } from '@/htmlDelta'
import { buildBlockManifest, buildFragmentManifest } from '@/renderTransport'

let transportEncoder = new TextEncoder()

export class Component {
    constructor(el) {
        if (el.__livewire) throw 'Component already initialized';

        el.__livewire = this

        this.el = el

        this.id = el.getAttribute('wire:id')

        this.key = el.getAttribute('wire:key')

        this.__livewireId = this.id // @legacy

        this.snapshotEncoded = el.getAttribute('wire:snapshot')

        this.snapshot = JSON.parse(this.snapshotEncoded)

        if (! this.snapshot) {
            throw `Snapshot missing on Livewire component with id: ` + this.id
        }

        this.name = this.snapshot.memo.name

        this.effects = JSON.parse(el.getAttribute('wire:effects'))
        this.renderTransportConfig = normalizeRenderTransportConfig(
            this.effects.renderTransport,
        )
        delete this.effects.renderTransport
        this.originalEffects = deepClone(this.effects)

        // The delta update engine needs the exact server-rendered string, not
        // the browser-normalized DOM. A full update seeds this baseline before
        // later requests can ask the server for deltas.
        this.serverRenderedHtml = null
        this.serverRenderedHtmlHash = null
        this.renderBaseline = null
        this.renderRevision = 0
        this.transportFullLosses = 0
        this.transportManifestCooldown = 0
        this.snapshotReference = null
        this.snapshotReferenceSnapshot = null
        this.snapshotReferenceCooldown = 0
        this.requestCompressionMinimumBytes = null
        this.htmlResyncPending = false
        this.htmlResyncPromise = null

        // "canonical" data represents the last known server state.
        this.canonical = extractData(deepClone(this.snapshot.data), { component: this })
        // "ephemeral" represents the most current state. (This can be freely manipulated by end users)
        this.ephemeral = extractData(deepClone(this.snapshot.data), { component: this })
        // "reactive" is just ephemeral, except when you mutate it, front-ends like Vue react.
        this.reactive = Alpine.reactive(this.ephemeral)

        this.queuedUpdates = {}

        this.jsActions = {}

        // this.$wire = this.reactive
        this.$wire = generateWireObject(this, this.reactive)

        // Set the $wire property on the root element...
        el.$wire = this.$wire

        this.cleanups = []

        // Effects will be processed after every request, but we'll also handle them on initialization.
        this.processEffects(this.effects)
    }

    addActionContext(context) {
        // New system: just set the origin for next action
        if (context.el || context.directive) {
            setNextActionOrigin({
                el: context.el,
                directive: context.directive
            })
        }

        // Note: Non-origin metadata should be passed directly to fireAction
        // This method is kept for backwards compatibility but simplified
    }

    intercept(action, callback = null) {
        return this.$wire.$intercept(action, callback)
    }

    mergeNewSnapshot(snapshotEncoded, effects, updates = {}) {
        let snapshot = JSON.parse(snapshotEncoded)

        // Canonical can contain rich synth values that don't survive JSON
        // cloning, so re-extract a fresh copy from the old raw snapshot...
        let oldCanonical = extractData(deepClone(this.snapshot.data), { component: this })
        let updatedOldCanonical = this.applyUpdates(oldCanonical, updates)

        let newCanonical = extractData(deepClone(snapshot.data), { component: this })

        let dirty = diff(updatedOldCanonical, newCanonical)

        this.snapshotEncoded = snapshotEncoded

        this.snapshot = snapshot

        this.effects = effects

        this.canonical = extractData(deepClone(snapshot.data), { component: this })

        let newData = extractData(deepClone(snapshot.data), { component: this })

        // Diff old vs new and patch differences onto the reactive proxy. Walks
        // the trees directly, avoiding dot-notated paths which break when
        // object keys contain dots.
        diffAndPatchRecursive(updatedOldCanonical, newData, this.reactive)

        return dirty
    }

    queueUpdate(propertyName, value) {
        // These updates will be applied first on the server
        // on the next request, then trickle back to the
        // client on the next request that gets sent.
        this.queuedUpdates[propertyName] = dehydrateTree(value)
    }

    mergeQueuedUpdates(diff) {
        // Before adding queuedUpdates into the diff list, we will remove any diffs
        // that will be overriden by the queued update. Queued updates will take
        // priority against ephemeral updates that have happend since them...
        Object.entries(this.queuedUpdates).forEach(([updateKey, updateValue]) => {
            Object.entries(diff).forEach(([diffKey, diffValue]) => {
                if (diffKey.startsWith(updateKey)) {
                    delete diff[diffKey]
                }
            })

            diff[updateKey] = updateValue
        })

        this.queuedUpdates = []

        return diff
    }

    getUpdates() {
        let propertiesDiff = diffAndConsolidate(this.canonical, this.ephemeral)

        return this.mergeQueuedUpdates(propertiesDiff)
    }

    getRenderMetadata(baseline = this.captureRenderBaseline()) {
        if (! supportsHtmlDeltaVerification()) return {}

        let config = this.renderTransportConfig

        // A page rendered by the original experimental delta server has no
        // mount handshake. Keep speaking its htmlHash protocol during rolling
        // deployments instead of sending v1 metadata it cannot understand.
        if (! config) {
            return baseline ? { htmlHash: baseline.hash } : {}
        }

        let capabilities = ['same']
        let canSendPortableManifests = this.transportManifestCooldown === 0
        let canUseSnapshotReferences = this.snapshotReferenceCooldown === 0

        if (this.transportManifestCooldown > 0) this.transportManifestCooldown--
        if (this.snapshotReferenceCooldown > 0) this.snapshotReferenceCooldown--

        if (config.snapshotDelta) capabilities.push('snapshot-delta')
        if (config.snapshotReferences && canUseSnapshotReferences) {
            capabilities.push('snapshot-ref')
        }

        let metadata = {
            v: 1,
            capabilities,
        }

        if (! baseline) return metadata

        if (baseline.portable && config.cacheAccelerator) {
            capabilities.push('splice')
        }

        metadata.base = {
            hash: baseline.hash,
            bytes: baseline.bytes,
            revision: baseline.revision,
        }

        if (canSendPortableManifests && baseline.chunks) {
            capabilities.push('chunks')
            metadata.chunks = baseline.chunks
        }

        if (canSendPortableManifests
            && baseline.fragments
            && baseline.fragments.nodes.length > 0
        ) {
            capabilities.push('fragments')
            metadata.fragments = baseline.fragments
        }

        return metadata
    }

    captureRenderBaseline() {
        let baseline = this.renderBaseline

        if (! baseline
            || baseline.html !== this.serverRenderedHtml
            || baseline.hash !== this.serverRenderedHtmlHash
        ) return null

        return baseline
    }

    captureSnapshotReference(allowed = true, snapshot = null) {
        if (! allowed
            || typeof this.snapshotReference !== 'string'
            || ! /^[A-Za-z0-9_-]{24}$/.test(this.snapshotReference)
            || typeof snapshot !== 'string'
            || snapshot !== this.snapshotReferenceSnapshot
        ) return null

        return this.snapshotReference
    }

    rememberServerRenderedHtml(
        html,
        hash,
        render = null,
        requestBaseline = null,
        attemptedPortable = false,
    ) {
        if (typeof html !== 'string' || ! isSha256Hash(hash)) {
            return this.forgetServerRenderedHtml()
        }

        if (render?.target && render.target !== hash) {
            return this.forgetServerRenderedHtml()
        }

        let previous = this.captureRenderBaseline()

        if (previous && previous.html === html && previous.hash === hash) {
            this.htmlResyncPending = false
            this.recordRenderTransportOutcome(render, requestBaseline, attemptedPortable)

            return
        }

        let bytes = transportEncoder.encode(html).length
        let chunks = null
        let fragments = null
        let config = this.renderTransportConfig
        let portable = config !== null
            && bytes >= config.minimumBytes
            && bytes <= config.maximumBytes

        if (portable) {
            try {
                chunks = buildBlockManifest(html, config.blockSize)

                if (chunks.blocks.length > config.maximumManifestBytes) {
                    chunks = null
                }
            } catch (error) {}

            try {
                fragments = buildFragmentManifest(html)

                if (fragments.nodes.length > config.maximumFragments) {
                    fragments = null
                }
            } catch (error) {}
        }

        let revision = ++this.renderRevision

        this.serverRenderedHtml = html
        this.serverRenderedHtmlHash = hash
        this.renderBaseline = Object.freeze({
            html,
            hash,
            bytes,
            revision,
            portable,
            chunks,
            fragments,
        })
        this.htmlResyncPending = false

        this.recordRenderTransportOutcome(render, requestBaseline, attemptedPortable)
    }

    forgetServerRenderedHtml() {
        this.serverRenderedHtml = null
        this.serverRenderedHtmlHash = null
        this.renderBaseline = null
    }

    requestHtmlResync(start) {
        if (this.htmlResyncPromise) return this.htmlResyncPromise

        this.forgetServerRenderedHtml()
        this.htmlResyncPending = true

        this.htmlResyncPromise = Promise.resolve()
            .then(start)
            .finally(() => {
                this.htmlResyncPending = false
                this.htmlResyncPromise = null
            })

        return this.htmlResyncPromise
    }

    rememberSnapshotReference(reference, snapshot) {
        this.snapshotReference = null
        this.snapshotReferenceSnapshot = null

        if (this.snapshotReferenceCooldown > 0
            || typeof reference !== 'string'
            || ! /^[A-Za-z0-9_-]{24}$/.test(reference)
            || typeof snapshot !== 'string'
        ) return

        this.snapshotReference = reference
        this.snapshotReferenceSnapshot = snapshot
    }

    rejectSnapshotReference(reference) {
        if (reference === null || this.snapshotReference === reference) {
            this.snapshotReference = null
            this.snapshotReferenceSnapshot = null
        }

        this.snapshotReferenceCooldown = Math.max(this.snapshotReferenceCooldown, 5)
    }

    rememberRequestCompression(minimumBytes) {
        if (minimumBytes === undefined || minimumBytes === null) {
            this.requestCompressionMinimumBytes = null

            return
        }

        if (! Number.isSafeInteger(minimumBytes)
            || minimumBytes < 1
            || minimumBytes > 16 * 1024 * 1024
        ) return

        this.requestCompressionMinimumBytes = minimumBytes
    }

    getRequestCompressionMinimumBytes() {
        return this.requestCompressionMinimumBytes
    }

    getMaximumRequestBytes() {
        return this.renderTransportConfig?.maximumRequestBytes ?? null
    }

    recordRenderTransportOutcome(render, requestBaseline, attemptedPortable) {
        if (! render || render.v !== 1) return

        let lost = render.mode === 'full'

        if (Number.isSafeInteger(render.stats?.full)
            && Number.isSafeInteger(render.stats?.selected)
        ) {
            lost = render.stats.selected >= render.stats.full
        }

        if (lost && requestBaseline && attemptedPortable) {
            this.transportFullLosses++

            if (this.transportFullLosses >= 3) {
                this.transportFullLosses = 0
                this.transportManifestCooldown = Math.max(this.transportManifestCooldown, 5)
            }

            return
        }

        if (! lost) this.transportFullLosses = 0
    }

    applyUpdates(object, updates) {
        for (let key in updates) {
            dataSet(object, key, updates[key])
        }

        return object
    }

    replayUpdate(snapshot, html) {
        let effects = { ...this.effects, html}

        this.mergeNewSnapshot(JSON.stringify(snapshot), effects)

        this.processEffects({ html })
    }

    /**
     * Here we'll take the new state and side effects from the
     * server and use them to update the existing data that
     * users interact with, triggering reactive effects.
     */
    processEffects(effects, request) {
        // This is for BC.
        trigger('effects', this, effects)

        trigger('effect', {
            component: this,
            effects,
            cleanup: i => this.addCleanup(i),
            request,
        })
    }

    get children() {
        let componentEl = this.el

        let children = []

        componentEl.querySelectorAll('[wire\\:id]').forEach(el => {
            let parentComponentEl = el.parentElement.closest('[wire\\:id]')

            if (parentComponentEl !== componentEl) return

            let componentInstance = el.__livewire

            if (! componentInstance) return

            children.push(componentInstance)
        })

        return children
    }

    get islands() {
        let islands = this.snapshot.memo.islands

        return islands
    }

    get parent() {
        return findComponentByEl(this.el.parentElement)
    }

    get isIsolated() {
        return this.snapshot.memo.isolate
    }

    get isLazy() {
        return this.snapshot.memo.lazyLoaded !== undefined
    }

    get hasBeenLazyLoaded() {
        return this.snapshot.memo.lazyLoaded === true
    }

    get isLazyIsolated() {
        return !! this.snapshot.memo.lazyIsolated
    }

    getDeepChildrenWithBindings(callback) {
        this.getDeepChildren(child => {
            if (child.isLazy && ! child.hasBeenLazyLoaded) return

            if (child.hasReactiveProps() || child.hasWireModelableBindings()) {
                callback(child)
            }
        })
    }

    hasReactiveProps() {
        let meta = this.snapshot.memo
        let props = meta.props

        return !! props
    }

    hasWireModelableBindings() {
        let meta = this.snapshot.memo
        let bindings = meta.bindings

        return !! bindings
    }

    getDeepChildren(callback) {
        this.children.forEach(child => {
            callback(child)

            child.getDeepChildren(callback)
        })
    }

    getEncodedSnapshotWithLatestChildrenMergedIn() {
        let { snapshotEncoded, children, snapshot } = this

        let childrenMemo = {}

        children.forEach(child => {
            childrenMemo[child.key] = [child.el.tagName.toLowerCase(), child.id]
        })

        return snapshotEncoded.replace(
            /"children":\{[^}]*\}/,
            `"children":${JSON.stringify(childrenMemo)}`
        )
    }

    inscribeSnapshotAndEffectsOnElement() {
        let el = this.el

        el.setAttribute('wire:snapshot', this.snapshotEncoded)

        // We need to re-register any event listeners that were originally registered...
        let effects = this.originalEffects.listeners
            ? { listeners: this.originalEffects.listeners }
            : {}

        // We need to re-register any url/query-string bindings...
        if (this.originalEffects.url) {
            effects.url = this.originalEffects.url
        }

        // We need to re-register any scripts that were originally registered...
        if (this.originalEffects.scripts) {
            effects.scripts = this.originalEffects.scripts;
        }

        // Preserve the opt-in handshake when this DOM is stored in the
        // wire:navigate back/forward cache and initialized again later.
        if (this.renderTransportConfig) {
            effects.renderTransport = this.renderTransportConfig
        }

        el.setAttribute('wire:effects', JSON.stringify(effects))

        el.setAttribute('wire:key', this.key)
    }

    addJsAction(name, action) {
        this.jsActions[name] = action
    }

    hasJsAction(name) {
        return this.jsActions[name] !== undefined
    }

    getJsAction(name) {
        return this.jsActions[name].bind(this.$wire)
    }

    getJsActions() {
        let actions = {}
        for (let key of Object.keys(this.jsActions)) {
            actions[key] = this.getJsAction(key)
        }
        return actions
    }

    // Called by JSON.stringify() on both $wire (via the Proxy) and the
    // Component instance directly. Without this, stringifying a Component
    // throws a circular reference error (el <-> component). Tools like
    // Laravel Boost trigger this when logging objects to the browser console.
    toJSON() {
        return {
            id: this.id,
            name: this.name,
            key: this.key,
            data: Object.fromEntries(Object.entries(this.ephemeral)),
        }
    }

    addCleanup(cleanup) {
        this.cleanups.push(cleanup)

        return () => {
            let index = this.cleanups.indexOf(cleanup)

            if (index === -1) return

            this.cleanups.splice(index, 1)
        }
    }

    cleanup() {
        delete this.el.__livewire

        while (this.cleanups.length > 0) {
            this.cleanups.pop()()
        }
    }
}

function normalizeRenderTransportConfig(value) {
    let maximumRequestBytes = value?.maximumRequestBytes ?? null

    if (value === null
        || typeof value !== 'object'
        || Array.isArray(value)
        || value.v !== 1
        || ! Number.isSafeInteger(value.minimumBytes)
        || value.minimumBytes < 0
        || ! Number.isSafeInteger(value.maximumBytes)
        || value.maximumBytes < value.minimumBytes
        || value.maximumBytes > 64 * 1024 * 1024
        || ! Number.isSafeInteger(value.blockSize)
        || value.blockSize < 256
        || value.blockSize > 65536
        || ! Number.isSafeInteger(value.maximumManifestBytes)
        || value.maximumManifestBytes < 0
        || value.maximumManifestBytes > 65536
        || ! Number.isSafeInteger(value.maximumFragments)
        || value.maximumFragments < 0
        || value.maximumFragments > 1024
        || typeof value.cacheAccelerator !== 'boolean'
        || typeof value.snapshotDelta !== 'boolean'
        || typeof value.snapshotReferences !== 'boolean'
        || (maximumRequestBytes !== null
            && (! Number.isSafeInteger(maximumRequestBytes)
                || maximumRequestBytes < 0
                || maximumRequestBytes > 2147483647))
    ) return null

    return Object.freeze({
        v: 1,
        minimumBytes: value.minimumBytes,
        maximumBytes: value.maximumBytes,
        blockSize: value.blockSize,
        maximumManifestBytes: value.maximumManifestBytes,
        maximumFragments: value.maximumFragments,
        cacheAccelerator: value.cacheAccelerator,
        snapshotDelta: value.snapshotDelta,
        snapshotReferences: value.snapshotReferences,
        maximumRequestBytes,
    })
}
