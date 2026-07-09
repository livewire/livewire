import Alpine from 'alpinejs'
import { getCsrfToken, dataGet, dataSet } from '@/utils'
import { TemporaryUpload, stashObjectUrl } from './synth'
import form from './strategies/form'
import chunked from './strategies/chunked'
import s3 from './strategies/s3'

let strategies = { form, chunked, s3 }

let uploadManagers = new WeakMap

export function getUploadManager(component) {
    // Rich upload objects living in reactive state reach for their component
    // through Alpine's proxies — unwrap so every caller resolves the same
    // manager instance (a duplicate would register duplicate listeners and
    // track uploads the original never sees)...
    component = Alpine.raw(component)

    if (! uploadManagers.has(component)) {
        let manager = new UploadManager(component)

        uploadManagers.set(component, manager)

        manager.registerListeners()
    }

    return uploadManagers.get(component)
}

export class UploadManager {
    constructor(component) {
        this.component = component
        this.uploadBag = new MessageBag
        this.removeBag = new MessageBag
        this.planWaiters = new Map
    }

    registerListeners() {
        this.component.$wire.$on('upload:plan', ({ name, plan }) => {
            // We have to add redundant "setLoading" calls because the dom-patch
            // from the first response will clear the setUploadLoading call
            // from the first upload call.
            setUploadLoading(this.component, name)

            this.handlePlan(name, plan)
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

        this.component.$wire.call('_removeUpload', name, tmpFilename)
    }

    setUpload(name, uploadObject) {
        this.uploadBag.add(name, uploadObject)

        if (this.uploadBag.get(name).length === 1) {
            this.startUpload(name, uploadObject)
        }
    }

    startUpload(name, uploadObject) {
        let fileInfos = uploadObject.files.map(file => {
            return { name: file.name, size: file.size, type: file.type, lastModified: file.lastModified }
        })

        this.setPendingUploads(name, uploadObject)

        // Return the roundtrip so refreshPlan() can release its waiter if the
        // call itself fails (network drop, expired session) rather than hang...
        let call = this.component.$wire.call('_startUpload', name, fileInfos, uploadObject.multiple)

        setUploadLoading(this.component, name)

        return call
    }

    async handlePlan(name, plan) {
        // A mid-upload re-handshake (expired signed URL) resolves the pending
        // waiter instead of kicking off a new upload...
        let waiter = this.planWaiters.get(name)

        if (waiter) {
            // Only feed the plan to the waiter if its upload is still the one in
            // flight. If the upload was cancelled/replaced in the meantime the
            // waiter is stale — release it so its strategy unwinds cleanly
            // instead of hanging forever (and wedging the whole property)...
            if (this.uploadBag.first(name) === waiter.uploadObject) {
                this.resolveWaiter(name, plan)
            } else {
                this.rejectWaiter(name, { type: 'abort' })
            }

            return
        }

        let uploadObject = this.uploadBag.first(name)

        if (! uploadObject) return

        // A plan already ran for this upload (e.g. a late-arriving refresh plan
        // whose waiter was already released) — don't start its strategy twice...
        if (uploadObject.planHandled) return

        uploadObject.planHandled = true

        if (plan.strategy === 'reject') {
            this.component.$wire.call('_uploadErrored', name, plan.errors, uploadObject.multiple)

            return
        }

        let strategy = strategies[plan.strategy]

        if (! strategy) {
            this.component.$wire.call('_uploadErrored', name, null, uploadObject.multiple)

            return
        }

        let csrfHeaders = this.csrfHeaders()

        let ctx = {
            plan,
            files: uploadObject.files,
            headers: csrfHeaders,
            csrfHeaders,
            uploadState: uploadObject,
            progress: this.makeProgressTracker(uploadObject),
            refreshPlan: () => this.refreshPlan(name, uploadObject),
        }

        try {
            let paths = await strategy(ctx)

            this.component.$wire.call('_finishUpload', name, paths, uploadObject.multiple, uploadObject.append)
        } catch (error) {
            // Cancelled uploads have already been cleaned up by cancelUpload()...
            if (error && error.type === 'abort') return

            if (error && error.type === 'status' && error.status === 422) {
                this.component.$wire.call('_uploadErrored', name, error.raw, uploadObject.multiple)

                return
            }

            this.component.$wire.call('_uploadErrored', name, null, uploadObject.multiple)
        }
    }

    // Optimistically place pending rich upload objects on the property so
    // frontends get reactive progress/isUploading/previewUrl state while
    // the upload is still in flight...
    setPendingUploads(name, uploadObject) {
        // Mid-upload re-handshakes call startUpload() again for the same
        // upload — only seed the pending objects once...
        if (uploadObject.pendings) return

        let context = { component: this.component, path: name }

        let pendings = uploadObject.files.map(file => TemporaryUpload.pending(file, context))

        uploadObject.previousValue = dataGet(this.component.ephemeral, name)

        if (uploadObject.multiple) {
            let existing = uploadObject.append ? (dataGet(this.component.ephemeral, name) || []) : []

            dataSet(this.component.reactive, name, [...existing, ...pendings])

            // Re-read through the reactive proxy so progress mutations trigger effects...
            uploadObject.pendings = dataGet(this.component.reactive, name).slice(-pendings.length)
        } else {
            dataSet(this.component.reactive, name, pendings[0])

            uploadObject.pendings = [dataGet(this.component.reactive, name)]
        }
    }

    // Restore the property to its pre-upload value (upload errored or was cancelled)...
    revertPendingUploads(name, uploadObject) {
        if (! uploadObject || ! uploadObject.pendings) return

        uploadObject.pendings.forEach(pending => {
            if (pending._objectUrl) URL.revokeObjectURL(pending._objectUrl)
        })

        dataSet(this.component.reactive, name, uploadObject.previousValue === undefined ? null : uploadObject.previousValue)
    }

    refreshPlan(name, uploadObject) {
        return new Promise((resolve, reject) => {
            // Backstop: if the fresh plan never arrives (a dropped event, a
            // dead roundtrip) release the waiter so the strategy errors instead
            // of hanging indefinitely...
            let timeout = setTimeout(() => this.rejectWaiter(name, { type: 'network' }), 30000)

            this.planWaiters.set(name, { uploadObject, resolve, reject, timeout })

            // If the _startUpload roundtrip itself fails, release the waiter...
            Promise.resolve(this.startUpload(name, uploadObject)).catch(() => {
                this.rejectWaiter(name, { type: 'network' })
            })
        })
    }

    resolveWaiter(name, plan) {
        let waiter = this.planWaiters.get(name)

        if (! waiter) return

        this.planWaiters.delete(name)

        clearTimeout(waiter.timeout)

        waiter.resolve(plan)
    }

    rejectWaiter(name, reason) {
        let waiter = this.planWaiters.get(name)

        if (! waiter) return

        this.planWaiters.delete(name)

        clearTimeout(waiter.timeout)

        waiter.reject(reason)
    }

    makeProgressTracker(uploadObject) {
        let total = uploadObject.files.reduce((sum, file) => sum + file.size, 0) || 1

        let tracker = {
            total,
            base: 0,
            report: inFlightBytes => {
                let loaded = Math.max(0, Math.min(tracker.base + inFlightBytes, total))
                let progress = Math.floor((loaded * 100) / total)

                ;(uploadObject.pendings || []).forEach(pending => pending._progress = progress)

                uploadObject.progressCallback({
                    loaded, total, detail: { progress, loaded, total },
                })
            },
            commit: bytes => {
                // Floor at 0 so a bogus (negative) chunk length from corrupted
                // server state can't send the progress bar backwards...
                tracker.base = Math.max(0, Math.min(tracker.base + bytes, total))

                tracker.report(0)
            },
            // For single-request strategies where the browser reports encoded
            // request bytes rather than raw file bytes...
            reportRatio: (loaded, rawTotal) => {
                tracker.report(Math.floor((loaded / (rawTotal || 1)) * total))
            },
        }

        return tracker
    }

    csrfHeaders() {
        let headers = {}

        let csrfToken = getCsrfToken()

        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken

        return headers
    }

    markUploadFinished(name, tmpFilenames) {
        unsetUploadLoading(this.component)

        let uploadObject = this.uploadBag.shift(name)

        // Graduate the pending objects: give them their wire value (flips
        // isUploading reactively) and keep their local blob URLs available
        // for previews after the server swaps in hydrated instances...
        ;(uploadObject.pendings || []).forEach((pending, i) => {
            if (! tmpFilenames[i]) return

            if (pending._objectUrl) stashObjectUrl(tmpFilenames[i], pending._objectUrl)

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

        // Release any pending re-handshake waiter so a cancel can't leave it
        // dangling (which would hang the strategy and swallow the next upload)...
        this.rejectWaiter(name, { type: 'abort' })

        let uploadItem = this.uploadBag.first(name)

        if (uploadItem) {
            uploadItem.cancelled = true

            if (uploadItem.request) {
                uploadItem.request.abort()
            }

            this.uploadBag.shift(name)

            this.revertPendingUploads(name, uploadItem)

            uploadItem.cancelledCallback()

            if (cancelledCallback) cancelledCallback()
        }
    }
}

export class MessageBag {
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
}

function setUploadLoading() {
    // @todo
}

function unsetUploadLoading() {
    // @todo
}
