import { getCsrfToken } from '@/utils';

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
        this.component.$wire.$on('upload:generatedSignedUrl', ({ name, url, chunkConfig }) => {
            // We have to add reduntant "setLoading" calls because the dom-patch
            // from the first response will clear the setUploadLoading call
            // from the first upload call.
            setUploadLoading(this.component, name)

            if (chunkConfig) {
                this.handleChunkedUpload(name, chunkConfig)
                return
            }

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

    handleChunkedUpload(name, chunkConfig) {
        let uploadObj = this.uploadBag.first(name)
        let file = uploadObj.files[0]
        let { chunkSize, retryDelays, initUrl } = chunkConfig

        let totalSize = file.size
        let offset = 0
        let patchUrl = null
        let offsetUrl = null
        let retryCount = 0
        let aborted = false
        let currentXhr = null
        let component = this.component

        // Wire up abort for cancelUpload()
        uploadObj.request = { abort: () => { aborted = true; if (currentXhr) currentXhr.abort() } }

        let csrfToken = getCsrfToken()

        // Step 1: POST to init endpoint to get transfer ID and signed URLs
        let initXhr = new XMLHttpRequest()
        currentXhr = initXhr
        initXhr.open('POST', initUrl)
        initXhr.setRequestHeader('Content-Type', 'application/json')
        initXhr.setRequestHeader('Upload-Length', totalSize)
        initXhr.setRequestHeader('Upload-Name', file.name)
        if (csrfToken) initXhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

        initXhr.addEventListener('load', () => {
            if (aborted) return

            if (initXhr.status !== 200) {
                uploadObj.errorCallback()
                return
            }

            let response = JSON.parse(initXhr.responseText)
            patchUrl = response.patchUrl
            offsetUrl = response.offsetUrl
            sendNextChunk()
        })

        initXhr.addEventListener('error', () => {
            if (!aborted) uploadObj.errorCallback()
        })

        initXhr.send()

        let self = this

        // Step 2: Send chunks sequentially via PATCH
        function sendNextChunk() {
            if (aborted) return
            if (offset >= totalSize) return

            let end = Math.min(offset + chunkSize, totalSize)
            let chunk = file.slice(offset, end)

            let xhr = new XMLHttpRequest()
            currentXhr = xhr
            xhr.open('PATCH', patchUrl)
            xhr.setRequestHeader('Content-Type', 'application/offset+octet-stream')
            xhr.setRequestHeader('Upload-Offset', offset)
            if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

            // Per-chunk progress → overall progress
            xhr.upload.addEventListener('progress', (e) => {
                if (!e.lengthComputable) return
                let totalSent = offset + e.loaded
                uploadObj.progressCallback({ loaded: totalSent, total: totalSize })
            })

            xhr.addEventListener('load', () => {
                if (aborted) return

                if (xhr.status === 204) {
                    let newOffset = parseInt(xhr.getResponseHeader('Upload-Offset'))
                    offset = newOffset
                    retryCount = 0

                    // Check if upload is complete
                    let complete = xhr.getResponseHeader('Upload-Complete')
                    if (complete === 'true') {
                        let signedPath = xhr.getResponseHeader('X-Signed-Path')
                        component.$wire.call('_finishUpload', name, [signedPath], uploadObj.multiple, uploadObj.append)
                        return
                    }

                    sendNextChunk()
                } else if (xhr.status === 409) {
                    // Offset mismatch — check server offset and retry
                    checkOffset(() => sendNextChunk())
                } else {
                    retryOrFail()
                }
            })

            xhr.addEventListener('error', () => {
                if (!aborted) retryOrFail()
            })

            xhr.send(chunk)
        }

        // Step 3: GET offset endpoint to check progress (for resume after failure)
        function checkOffset(callback) {
            let xhr = new XMLHttpRequest()
            currentXhr = xhr
            xhr.open('GET', offsetUrl)
            if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

            xhr.addEventListener('load', () => {
                if (aborted) return

                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText)
                    offset = response.offset
                    callback()
                } else {
                    uploadObj.errorCallback()
                }
            })

            xhr.addEventListener('error', () => {
                if (!aborted) uploadObj.errorCallback()
            })

            xhr.send()
        }

        // Step 4: Retry with backoff
        function retryOrFail() {
            if (retryCount < retryDelays.length) {
                let delay = retryDelays[retryCount++]
                setTimeout(() => {
                    checkOffset(() => sendNextChunk())
                }, delay)
            } else {
                self.component.$wire.call('_uploadErrored', name, null, uploadObj.multiple)
            }
        }
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

        this.component.$wire.call('_startUpload', name, fileInfos, uploadObject.multiple);

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

        let uploadItem = this.uploadBag.first(name);

        if (uploadItem) {
            if (uploadItem.request) {
                uploadItem.request.abort();
            }

            this.uploadBag.shift(name).cancelledCallback();

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
