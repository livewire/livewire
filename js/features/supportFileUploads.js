import { getCsrfToken } from '@/utils'

let uploadManagers = new WeakMap

function getUploadManager(component) {
    if (! uploadManagers.has(component)) {
        let manager = new UploadManager(component)

        uploadManagers.set(component, manager)

        manager.registerListeners()
    }

    return uploadManagers.get(component)
}

export function handleFileUpload(el, property, component, cleanup, options = {}) {
    let manager = getUploadManager(component)

    let start = () => el.dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true, detail: { id: component.id, property} }))
    let finish = () => el.dispatchEvent(new CustomEvent('livewire-upload-finish', { bubbles: true, detail: { id: component.id, property} }))
    let error = () => el.dispatchEvent(new CustomEvent('livewire-upload-error', { bubbles: true, detail: { id: component.id, property} }))
    let cancel = () => el.dispatchEvent(new CustomEvent('livewire-upload-cancel', { bubbles: true, detail: { id: component.id, property} }))
    let progress = (progressEvent) => {
        let percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total )

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
            manager.uploadMultiple(property, e.target.files, finish, error, progress, cancel, true, options)
        } else {
            manager.upload(property, e.target.files[0], finish, error, progress, cancel, options)
        }
    }

    el.addEventListener('change', eventHandler)

    // If the Livewire property has changed to null or an empty string, then reset the input...
    component.$wire.$watch(property, (value) => {
        // This watch will only be released when the component is removed. However, the
        // actual file-upload element may be removed from the DOM withou the entire
        // component being removed. In this case, let's just bail early on this.
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

        this.component.$wire.$on('upload:generatedSignedChunkUrl', ({ name, url, uploadId }) => {
            setUploadLoading(this.component, name)

            this.handleSignedChunkUrl(name, url, uploadId)
        })

        this.component.$wire.$on('upload:finished', ({ name, tmpFilenames }) => this.markUploadFinished(name, tmpFilenames))
        this.component.$wire.$on('upload:errored', ({ name }) => this.markUploadErrored(name))
        this.component.$wire.$on('upload:removed', ({ name, tmpFilename }) => this.removeBag.shift(name).finishCallback(tmpFilename))
    }

    upload(name, file, finishCallback, errorCallback, progressCallback, cancelledCallback, options = {}) {
        this.setUpload(name, {
            files: [file],
            multiple: false,
            finishCallback,
            errorCallback,
            progressCallback,
            cancelledCallback,
            append: false,
            options,
        })
    }

    uploadMultiple(name, files, finishCallback, errorCallback, progressCallback, cancelledCallback, append = true, options = {}) {
        this.setUpload(name, {
            files: Array.from(files),
            multiple: true,
            finishCallback,
            errorCallback,
            progressCallback,
            cancelledCallback,
            append,
            options,
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

    handleSignedChunkUrl(name, url, uploadId) {
        let uploadObject = this.uploadBag.first(name)
        let fileIndex = uploadObject.currentFileIndex || 0
        let file = uploadObject.files[fileIndex]
        let chunkSize = (uploadObject.options && uploadObject.options.chunkSize) || (2 * 1024 * 1024)
        let totalChunks = Math.ceil(file.size / chunkSize)

        uploadObject.chunkUploadId = uploadId
        uploadObject.completedPaths = uploadObject.completedPaths || []

        this.uploadChunkSequence(name, url, uploadId, file, 0, totalChunks, chunkSize, fileIndex)
    }

    uploadChunkSequence(name, url, uploadId, file, chunkIndex, totalChunks, chunkSize, fileIndex) {
        let uploadObject = this.uploadBag.first(name)
        if (!uploadObject) return

        let start = chunkIndex * chunkSize
        let end = Math.min(start + chunkSize, file.size)
        let chunk = file.slice(start, end)

        let formData = new FormData()
        formData.append('chunk', chunk, file.name)
        formData.append('uploadId', uploadId)
        formData.append('chunkIndex', chunkIndex)
        formData.append('totalChunks', totalChunks)

        let headers = { 'Accept': 'application/json' }
        let csrfToken = getCsrfToken()
        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken

        let request = new XMLHttpRequest()
        request.open('post', url)

        Object.entries(headers).forEach(([key, value]) => {
            request.setRequestHeader(key, value)
        })

        // Calculate total size across all files for multi-file progress
        let totalAllFilesSize = uploadObject.files.reduce((sum, f) => sum + f.size, 0)
        let completedFilesSize = uploadObject.files.slice(0, fileIndex).reduce((sum, f) => sum + f.size, 0)

        request.upload.addEventListener('progress', e => {
            let chunkProgress = e.loaded / e.total
            let currentFileLoaded = (chunkIndex * chunkSize) + (chunkProgress * (end - start))
            let overallLoaded = completedFilesSize + currentFileLoaded
            let overallProgress = Math.floor((overallLoaded * 100) / totalAllFilesSize)

            let progressEvent = { detail: { progress: overallProgress }, loaded: overallLoaded, total: totalAllFilesSize }
            uploadObject.progressCallback(progressEvent)
        })

        request.addEventListener('load', () => {
            if ((request.status + '')[0] === '2') {
                let response = JSON.parse(request.response)

                if (response.path) {
                    // Final chunk for this file — server has assembled it
                    uploadObject.completedPaths.push(response.path)

                    let nextFileIndex = fileIndex + 1

                    if (nextFileIndex < uploadObject.files.length) {
                        // More files to upload — start chunked upload for next file
                        uploadObject.currentFileIndex = nextFileIndex
                        this.component.$wire.call('_startChunkedUpload', name, [{ name: uploadObject.files[nextFileIndex].name, size: uploadObject.files[nextFileIndex].size, type: uploadObject.files[nextFileIndex].type }], uploadObject.multiple)
                    } else {
                        // All files done — call _finishUpload with all paths
                        this.component.$wire.call('_finishUpload', name, uploadObject.completedPaths, uploadObject.multiple, uploadObject.append)
                    }
                } else {
                    // More chunks to send for this file
                    this.uploadChunkSequence(name, url, uploadId, file, chunkIndex + 1, totalChunks, chunkSize, fileIndex)
                }
                return
            }

            let errors = null

            if (request.status === 422) {
                errors = request.response
            }

            this.component.$wire.call('_uploadErrored', name, errors, uploadObject.multiple)
        })

        request.addEventListener('error', () => {
            this.component.$wire.call('_uploadErrored', name, null, uploadObject.multiple)
        })

        uploadObject.request = request

        request.send(formData)
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

        this.uploadBag.first(name).request = request

        request.send(formData)
    }

    startUpload(name, uploadObject) {
        let fileInfos = uploadObject.files.map(file => {
            return { name: file.name, size: file.size, type: file.type }
        })

        if (uploadObject.options && uploadObject.options.chunked) {
            this.component.$wire.call('_startChunkedUpload', name, fileInfos, uploadObject.multiple)
        } else {
            this.component.$wire.call('_startUpload', name, fileInfos, uploadObject.multiple)
        }

        setUploadLoading(this.component, name)
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
            if (uploadItem.request) {
                uploadItem.request.abort()
            }

            // If this was a chunked upload, notify server to clean up
            if (uploadItem.options && uploadItem.options.chunked && uploadItem.chunkUploadId) {
                this.component.$wire.call('_cancelChunkedUpload', uploadItem.chunkUploadId)
            }

            this.uploadBag.shift(name).cancelledCallback()

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
    options = {},
) {
    let uploadManager = getUploadManager(component)

    uploadManager.upload(
        name,
        file,
        finishCallback,
        errorCallback,
        progressCallback,
        cancelledCallback,
        options,
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
    options = {},
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
        options,
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
