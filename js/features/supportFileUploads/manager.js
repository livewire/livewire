import { getCsrfToken } from '@/utils'
import form from './strategies/form'
import chunked from './strategies/chunked'
import s3 from './strategies/s3'

let strategies = { form, chunked, s3 }

let uploadManagers = new WeakMap

export function getUploadManager(component) {
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

        this.component.$wire.call('_startUpload', name, fileInfos, uploadObject.multiple)

        setUploadLoading(this.component, name)
    }

    async handlePlan(name, plan) {
        // A mid-upload re-handshake (expired signed URL) resolves the pending
        // waiter instead of kicking off a new upload...
        let waiter = this.planWaiters.get(name)

        if (waiter) {
            this.planWaiters.delete(name)

            waiter(plan)

            return
        }

        let uploadObject = this.uploadBag.first(name)

        if (! uploadObject) return

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

    refreshPlan(name, uploadObject) {
        return new Promise(resolve => {
            this.planWaiters.set(name, resolve)

            this.startUpload(name, uploadObject)
        })
    }

    makeProgressTracker(uploadObject) {
        let total = uploadObject.files.reduce((sum, file) => sum + file.size, 0) || 1

        let tracker = {
            total,
            base: 0,
            report: inFlightBytes => {
                let loaded = Math.min(tracker.base + inFlightBytes, total)
                let progress = Math.floor((loaded * 100) / total)

                uploadObject.progressCallback({
                    loaded, total, detail: { progress, loaded, total },
                })
            },
            commit: bytes => {
                tracker.base = Math.min(tracker.base + bytes, total)

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
        uploadObject.finishCallback(uploadObject.multiple ? tmpFilenames : tmpFilenames[0])

        if (this.uploadBag.get(name).length > 0) this.startUpload(name, this.uploadBag.last(name))
    }

    markUploadErrored(name) {
        unsetUploadLoading(this.component)

        this.uploadBag.shift(name).errorCallback()

        if (this.uploadBag.get(name).length > 0) this.startUpload(name, this.uploadBag.last(name))
    }

    cancelUpload(name, cancelledCallback = null) {
        unsetUploadLoading(this.component)

        let uploadItem = this.uploadBag.first(name)

        if (uploadItem) {
            uploadItem.cancelled = true

            if (uploadItem.request) {
                uploadItem.request.abort()
            }

            this.uploadBag.shift(name).cancelledCallback()

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
