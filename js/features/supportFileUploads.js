import { getCsrfToken, dataGet, dataSet } from '@/utils';
import { registerSynth } from '@/synths';

// Client-side blob URLs for uploaded files, keyed by their temporary server
// filename. Lets previews keep using the local file after the upload
// finishes instead of fetching the file back from the server...
let objectUrls = new Map

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

    get isPreviewable() { return this.meta.previewUrl !== undefined || this.file !== null }

    // The best available preview URL: the local file's blob URL when we have
    // it (instant, no server request), otherwise the signed server URL...
    get previewUrl() {
        if (this.file) return this._objectUrl ??= URL.createObjectURL(this.file)

        if (this.filename && objectUrls.has(this.filename)) return objectUrls.get(this.filename)

        return this.meta.previewUrl ?? null
    }

    // The signed server-side preview URL (equivalent of PHP's temporaryUrl())...
    temporaryUrl() { return this.meta.previewUrl ?? null }

    // Remove this upload from the property it lives on. Cancels the upload
    // if it's still in flight...
    remove(finishCallback = () => {}) {
        if (! this.context) throw 'Cannot remove an upload that isn\'t attached to a component property'

        if (this.isUploading) {
            return cancelUpload(this.context.component, this._propertyName)
        }

        if (objectUrls.has(this.filename)) {
            URL.revokeObjectURL(objectUrls.get(this.filename))
            objectUrls.delete(this.filename)
        }

        return removeUpload(this.context.component, this._propertyName, this.filename, finishCallback)
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

let uploadManagers = new WeakMap

function getUploadManager(component) {
    if (! uploadManagers.has(component)) {
        let manager = new UploadManager(component)

        uploadManagers.set(component, manager)

        manager.registerListeners()
    }

    return uploadManagers.get(component)
}

export function handleFileUpload(el, property, component, cleanup) {
    let manager = getUploadManager(component)

    let start = () => el.dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true, detail: { id: component.id, property} }))
    let finish = () => el.dispatchEvent(new CustomEvent('livewire-upload-finish', { bubbles: true, detail: { id: component.id, property} }))
    let error = () => el.dispatchEvent(new CustomEvent('livewire-upload-error', { bubbles: true, detail: { id: component.id, property} }))
    let cancel = () => el.dispatchEvent(new CustomEvent('livewire-upload-cancel', { bubbles: true, detail: { id: component.id, property} }))
    let progress = (progressEvent) => {
        var percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total )

        el.dispatchEvent(
            new CustomEvent('livewire-upload-progress', {
                bubbles: true, detail: { progress: percentCompleted }
            })
        )
    }

    let eventHandler = e => {
        if (e.target.files.length === 0) return

        start()

        if (e.target.multiple) {
            manager.uploadMultiple(property, e.target.files, finish, error, progress, cancel)
        } else {
            manager.upload(property, e.target.files[0], finish, error, progress, cancel)
        }
    }

    el.addEventListener('change', eventHandler)

    // If the Livewire property has changed to null or an empty string, then reset the input...
    let unwatch = component.$wire.$watch(property, (value) => {
        // Alpine may have already queued this callback before the input was removed, so return early.
        if (! el.isConnected) return

        if (value === null || value === '') {
            el.value = ''
        }
        
        // If the file input is a multiple file input and the value has been reset to an empty array, then reset the input...
        if (el.multiple && Array.isArray(value) && value.length === 0) {
            el.value = ''
        }
    })

    // There's a bug in browsers where selecting a file, removing it,
    // then re-adding it doesn't fire the change event. This fixes it.
    // Reference: https://stackoverflow.com/questions/12030686/html-input-file-selection-event-not-firing-upon-selecting-the-same-file
    let clearFileInputValue = () => { el.value = null }
    el.addEventListener('click', clearFileInputValue)

    // Clear the input if the uploaded is cancelled...
    el.addEventListener('livewire-upload-cancel', clearFileInputValue)

    cleanup(() => {
        el.removeEventListener('change', eventHandler)
        el.removeEventListener('click', clearFileInputValue)
        el.removeEventListener('livewire-upload-cancel', clearFileInputValue)

        unwatch()
    })
}

class UploadManager {
    constructor(component) {
        this.component = component
        this.uploadBag = new MessageBag
        this.removeBag = new MessageBag
    }

    registerListeners() {
        this.component.$wire.$on('upload:generatedSignedUrl', ({ name, url }) => {
            // We have to add reduntant "setLoading" calls because the dom-patch
            // from the first response will clear the setUploadLoading call
            // from the first upload call.
            setUploadLoading(this.component, name)

            this.handleSignedUrl(name, url)
        })

        this.component.$wire.$on('upload:generatedSignedUrlForS3', ({ name, payload }) => {
            setUploadLoading(this.component, name)

            this.handleS3PreSignedUrl(name, payload)
        })

        this.component.$wire.$on('upload:finished', ({ name, tmpFilenames }) => this.markUploadFinished(name, tmpFilenames))
        this.component.$wire.$on('upload:errored', ({ name }) => this.markUploadErrored(name))
        this.component.$wire.$on('upload:removed', ({ name, tmpFilename }) => this.removeBag.shift(name).finishCallback(tmpFilename))
    }

    upload(name, file, finishCallback, errorCallback, progressCallback, cancelledCallback) {
        this.setUpload(name, {
            files: [file],
            multiple: false,
            finishCallback,
            errorCallback,
            progressCallback,
            cancelledCallback,
            append: false,
        })
    }

    uploadMultiple(name, files, finishCallback, errorCallback, progressCallback, cancelledCallback, append = true) {
        this.setUpload(name, {
            files: Array.from(files),
            multiple: true,
            finishCallback,
            errorCallback,
            progressCallback,
            cancelledCallback,
            append,
        })
    }

    removeUpload(name, tmpFilename, finishCallback) {
        this.removeBag.push(name, {
            tmpFilename, finishCallback
        })

        this.component.$wire.call('_removeUpload', name, tmpFilename);
    }

    setUpload(name, uploadObject) {
        this.uploadBag.add(name, uploadObject)

        if (this.uploadBag.get(name).length === 1) {
            this.startUpload(name, uploadObject)
        }
    }

    handleSignedUrl(name, url) {
        let formData = new FormData()
        Array.from(this.uploadBag.first(name).files).forEach(file => formData.append('files[]', file, file.name))

        let headers = {
            'Accept': 'application/json',
        }

        let csrfToken = getCsrfToken()

        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken

        this.makeRequest(name, formData, 'post', url, headers, response => {
            return response.paths
        })
    }

    handleS3PreSignedUrl(name, payload) {
        let formData = this.uploadBag.first(name).files[0]

        let headers = payload.headers
        if ('Host' in headers) delete headers.Host
        let url = payload.url

        this.makeRequest(name, formData, 'put', url, headers, response => {
            return [payload.path]
        })
    }

    makeRequest(name, formData, method, url, headers, retrievePaths) {
        let request = new XMLHttpRequest()

        request.open(method, url)

        Object.entries(headers).forEach(([key, value]) => {
            request.setRequestHeader(key, value)
        })

        request.upload.addEventListener('progress', e => {
            e.detail = {}
            e.detail.progress = Math.floor((e.loaded * 100) / e.total)

            this.updatePendingProgress(name, e.detail.progress)

            this.uploadBag.first(name).progressCallback(e)
        })

        request.addEventListener('load', () => {
            if ((request.status+'')[0] === '2') {
                let paths = retrievePaths(request.response && JSON.parse(request.response))

                this.component.$wire.call('_finishUpload', name, paths, this.uploadBag.first(name).multiple, this.uploadBag.first(name).append)

                return
            }

            let errors = null

            if (request.status === 422) {
                errors = request.response
            }

            this.component.$wire.call('_uploadErrored', name, errors, this.uploadBag.first(name).multiple)
        })

        request.addEventListener('error', () => {
            this.component.$wire.call('_uploadErrored', name, null, this.uploadBag.first(name).multiple)
        })

        this.uploadBag.first(name).request = request

        request.send(formData)
    }

    startUpload(name, uploadObject) {
        let fileInfos = uploadObject.files.map(file => {
            return { name: file.name, size: file.size, type: file.type }
        })

        this.setPendingUploads(name, uploadObject)

        this.component.$wire.call('_startUpload', name, fileInfos, uploadObject.multiple);

        setUploadLoading(this.component, name)
    }

    // Optimistically place pending rich upload objects on the property so
    // frontends get reactive progress/isUploading/previewUrl state while
    // the upload is still in flight...
    setPendingUploads(name, uploadObject) {
        let context = { component: this.component, path: name }

        let pendings = uploadObject.files.map(file => TemporaryUpload.pending(file, context))

        uploadObject.previousValue = dataGet(this.component.ephemeral, name)

        if (uploadObject.multiple) {
            let existing = uploadObject.append ? (dataGet(this.component.ephemeral, name) || []) : []

            dataSet(this.component.reactive, name, [...existing, ...pendings])

            uploadObject.pendings = dataGet(this.component.reactive, name).slice(-pendings.length)
        } else {
            dataSet(this.component.reactive, name, pendings[0])

            uploadObject.pendings = [dataGet(this.component.reactive, name)]
        }
    }

    updatePendingProgress(name, progress) {
        let uploadObject = this.uploadBag.first(name)

        if (! uploadObject || ! uploadObject.pendings) return

        uploadObject.pendings.forEach(pending => pending._progress = progress)
    }

    // Restore the property to its pre-upload value (upload errored or was cancelled)...
    revertPendingUploads(name, uploadObject) {
        if (! uploadObject || ! uploadObject.pendings) return

        uploadObject.pendings.forEach(pending => {
            if (pending._objectUrl) URL.revokeObjectURL(pending._objectUrl)
        })

        dataSet(this.component.reactive, name, uploadObject.previousValue === undefined ? null : uploadObject.previousValue)
    }

    markUploadFinished(name, tmpFilenames) {
        unsetUploadLoading(this.component)

        let uploadObject = this.uploadBag.shift(name)

        // Graduate the pending objects: give them their wire value (flips
        // isUploading reactively) and keep their local blob URLs available
        // for previews after the server swaps in hydrated instances...
        ;(uploadObject.pendings || []).forEach((pending, i) => {
            if (! tmpFilenames[i]) return

            if (pending._objectUrl) objectUrls.set(tmpFilenames[i], pending._objectUrl)

            pending.serialized = 'livewire-file:' + tmpFilenames[i]
        })

        uploadObject.finishCallback(uploadObject.multiple ? tmpFilenames : tmpFilenames[0])

        if (this.uploadBag.get(name).length > 0) this.startUpload(name, this.uploadBag.last(name))
    }

    markUploadErrored(name) {
        unsetUploadLoading(this.component)

        let uploadObject = this.uploadBag.shift(name)

        this.revertPendingUploads(name, uploadObject)

        uploadObject.errorCallback()

        if (this.uploadBag.get(name).length > 0) this.startUpload(name, this.uploadBag.last(name))
    }

    cancelUpload(name, cancelledCallback = null) {
        unsetUploadLoading(this.component)

        let uploadItem = this.uploadBag.first(name);

        if (uploadItem) {
            if (uploadItem.request) {
                uploadItem.request.abort();
            }

            this.uploadBag.shift(name).cancelledCallback();

            this.revertPendingUploads(name, uploadItem)

            if (cancelledCallback) cancelledCallback()
        }
    }
}

export default class MessageBag {
    constructor() {
        this.bag = {}
    }

    add(name, thing) {
        if (! this.bag[name]) {
            this.bag[name] = []
        }

        this.bag[name].push(thing)
    }

    push(name, thing) {
        this.add(name, thing)
    }

    first(name) {
        if (! this.bag[name]) return null

        return this.bag[name][0]
    }

    last(name) {
        return this.bag[name].slice(-1)[0]
    }

    get(name) {
        return this.bag[name]
    }

    shift(name) {
        return this.bag[name].shift()
    }

    call(name, ...params) {
        (this.listeners[name] || []).forEach(callback => {
            callback(...params)
        })
    }

    has(name) {
        return Object.keys(this.listeners).includes(name)
    }
}

function setUploadLoading() {
    // @todo
}

function unsetUploadLoading() {
    // @todo
}

export function upload(
    component,
    name,
    file,
    finishCallback = () => { },
    errorCallback = () => { },
    progressCallback = () => { },
    cancelledCallback = () => { },
) {
    let uploadManager = getUploadManager(component)

    uploadManager.upload(
        name,
        file,
        finishCallback,
        errorCallback,
        progressCallback,
        cancelledCallback,
    )
}

export function uploadMultiple(
    component,
    name,
    files,
    finishCallback = () => { },
    errorCallback = () => { },
    progressCallback = () => { },
    cancelledCallback = () => { },
    append = true,
) {
    let uploadManager = getUploadManager(component)

    uploadManager.uploadMultiple(
        name,
        files,
        finishCallback,
        errorCallback,
        progressCallback,
        cancelledCallback,
        append,
    )
}

export function removeUpload(
    component,
    name,
    tmpFilename,
    finishCallback = () => { },
    errorCallback = () => { }
) {
    let uploadManager = getUploadManager(component)

    uploadManager.removeUpload(
        name,
        tmpFilename,
        finishCallback,
        errorCallback
    )
}

export function cancelUpload(
    component,
    name,
    cancelledCallback = () => { }
) {
    let uploadManager = getUploadManager(component)

    uploadManager.cancelUpload(
        name,
        cancelledCallback
    )
}
