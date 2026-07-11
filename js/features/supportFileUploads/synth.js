import { registerSynth } from '@/synths'
import { getUploadManager } from './manager'

// Client-side blob URLs for uploaded files, keyed by their temporary server
// filename. Lets previews keep using the local file after the upload
// finishes instead of fetching the file back from the server...
let objectUrls = new Map

export function stashObjectUrl(filename, url) {
    objectUrls.set(filename, url)
}

export function releaseObjectUrl(filename) {
    if (! objectUrls.has(filename)) return

    URL.revokeObjectURL(objectUrls.get(filename))

    objectUrls.delete(filename)
}

/**
 * The rich JS counterpart to PHP's TemporaryUploadedFile. Wraps the raw
 * "livewire-file:..." wire value so file properties are useful objects
 * on the frontend instead of opaque serialized strings.
 *
 * Also represents in-flight uploads: while a file is uploading, the property
 * optimistically holds a pending instance exposing reactive progress state.
 */
export class TemporaryUpload {
    constructor(serialized, meta = {}, context = undefined) {
        this.serialized = serialized
        this.meta = meta
        this.context = context
        this.file = null
        this._progress = 0
        this._objectUrl = null
    }

    // An optimistic instance for an in-flight upload, created from the
    // browser's native File object before the server knows anything...
    static pending(file, context) {
        let instance = new TemporaryUpload(null, {}, context)

        instance.file = file

        return instance
    }

    get isUploading() { return this.serialized === null }

    get progress() { return this.isUploading ? this._progress : 100 }

    // The original filename from the user's machine...
    get name() { return this.file?.name ?? this.meta.name ?? this.filename }

    // The hashed temporary filename on the server (used by $wire.removeUpload())...
    get filename() { return this.serialized === null ? null : this.serialized.replace('livewire-file:', '') }

    get extension() { return this.name?.split('.').pop() }

    get isPreviewable() { return this.meta.url !== undefined || this.file !== null }

    // The best available preview URL: the local file's blob URL when we have
    // it (instant, no server request), otherwise the signed server URL...
    get previewUrl() {
        if (this.file) return this._objectUrl ??= URL.createObjectURL(this.file)

        if (this.filename && objectUrls.has(this.filename)) return objectUrls.get(this.filename)

        return this.meta.url ?? null
    }

    // The signed server-side preview URL (equivalent of PHP's temporaryUrl())...
    temporaryUrl() { return this.meta.url ?? null }

    // Remove this upload from the property it lives on, instantly. Cancels
    // the upload if it's still in flight; otherwise the property updates
    // optimistically and the server confirms in the background...
    remove(finishCallback = () => {}) {
        if (! this.context) throw 'Cannot remove an upload that isn\'t attached to a component property'

        let manager = getUploadManager(this.context.component)

        if (this.isUploading) {
            return manager.cancelUpload(this._propertyName)
        }

        return manager.removeUpload(this._propertyName, this.filename, finishCallback)
    }

    // The property this upload lives on: its state path minus any trailing
    // array index ('photos.1' → 'photos')...
    get _propertyName() {
        let segments = this.context.path.split('.')

        if (/^\d+$/.test(segments[segments.length - 1])) segments.pop()

        return segments.join('.')
    }

    // Degrade to the raw wire value when stringified or JSON-serialized...
    toString() { return this.serialized ?? '' }

    toJSON() { return this.serialized }
}

registerSynth('fil', {
    match: (value) => value instanceof TemporaryUpload,

    hydrate: (value, meta, context) => {
        if (typeof value !== 'string' || value === '') return value

        // Legacy multiple-file format: hydrate into an array of rich objects...
        if (value.startsWith('livewire-files:')) {
            return JSON.parse(value.replace('livewire-files:', '')).map(
                filename => new TemporaryUpload('livewire-file:' + filename, {}, context)
            )
        }

        return new TemporaryUpload(value, meta, context)
    },

    // Pending uploads have no wire representation yet (undefined tells
    // Livewire to never send them to the server)...
    dehydrate: (value) => value.serialized ?? undefined,
})
