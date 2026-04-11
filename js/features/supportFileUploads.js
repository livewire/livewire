import { getCsrfToken } from '@/utils';

// Base64 encode a filename so it's safe to send as an HTTP header value.
// XHR's setRequestHeader throws InvalidCharacterError on non-ASCII characters,
// and the tus protocol uses base64 for similar metadata for the same reason.
function encodeFilename(filename) {
    // Encode as UTF-8 bytes first (otherwise btoa breaks on multi-byte chars),
    // then base64. unescape(encodeURIComponent(...)) is the well-known idiom.
    return btoa(unescape(encodeURIComponent(filename)))
}

// Extract a JSON error body from an XHR response, if present.
// Returns the raw JSON string (matching the existing _uploadErrored API),
// or null if the response wasn't a structured error.
function extractErrorBody(xhr) {
    if (! xhr.responseText) return null

    try {
        // Validate it's actually JSON before passing it through
        JSON.parse(xhr.responseText)
        return xhr.responseText
    } catch (e) {
        return null
    }
}

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

        this.component.$wire.$on('upload:generatedSignedUrlForS3', ({ name, payloads }) => {
            setUploadLoading(this.component, name)

            this.handleS3Upload(name, payloads)
        })

        this.component.$wire.$on('upload:generatedSignedUrlForS3Multipart', ({ name, config }) => {
            setUploadLoading(this.component, name)

            this.handleS3Multipart(name, config)
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

    handleS3Upload(name, payloads) {
        let files = Array.from(this.uploadBag.first(name).files)

        // The server mints one presigned PUT URL per file. If the counts
        // disagree we bail out before sending any bytes.
        if (files.length !== payloads.length) {
            this.component.$wire.call('_uploadErrored', name, null, this.uploadBag.first(name).multiple)
            return
        }

        this.processFilesSequentially(name, (file, index, { onProgress, onComplete, onError, isAborted, setCurrentXhr }) => {
            let payload = payloads[index]
            let headers = { ...payload.headers }
            if ('Host' in headers) delete headers.Host

            let xhr = new XMLHttpRequest()
            setCurrentXhr(xhr)
            xhr.open('PUT', payload.url)

            Object.entries(headers).forEach(([key, value]) => {
                xhr.setRequestHeader(key, value)
            })

            xhr.upload.addEventListener('progress', (e) => {
                if (!e.lengthComputable) return
                onProgress(e.loaded)
            })

            xhr.addEventListener('load', () => {
                if (isAborted()) return

                if ((xhr.status + '')[0] === '2') {
                    onComplete(payload.path)
                    return
                }

                onError(xhr.status === 422 ? xhr.response : null)
            })

            xhr.addEventListener('error', () => {
                if (isAborted()) return
                onError(null)
            })

            xhr.send(file)
        })
    }

    handleChunkedUpload(name, chunkConfig) {
        let csrfToken = getCsrfToken()

        this.processFilesSequentially(name, (file, index, callbacks) => {
            this.uploadSingleChunked(file, name, chunkConfig, csrfToken, callbacks)
        })
    }

    handleS3Multipart(name, config) {
        let abortUrls = new Set()

        this.processFilesSequentially(name, (file, index, callbacks) => {
            this.uploadSingleS3Multipart(file, config, callbacks, abortUrls)
        })

        // Wrap abort to also fire beacons for S3 multipart cleanup.
        let uploadObj = this.uploadBag.first(name)
        let originalAbort = uploadObj.request.abort
        uploadObj.request.abort = () => {
            originalAbort()
            for (let url of abortUrls) navigator.sendBeacon(url)
        }
    }

    uploadSingleS3Multipart(file, config, callbacks, abortUrls) {
        let { chunkSize, retryDelays, initUrl } = config
        let { onProgress, onComplete, onError, isAborted, setCurrentXhr } = callbacks
        let csrfToken = getCsrfToken()

        let initXhr = new XMLHttpRequest()
        setCurrentXhr(initXhr)
        initXhr.open('POST', initUrl)
        initXhr.setRequestHeader('Accept', 'application/json')
        initXhr.setRequestHeader('Upload-Length', file.size)
        initXhr.setRequestHeader('Upload-Name', encodeFilename(file.name))
        initXhr.setRequestHeader('Upload-Type', file.type || 'application/octet-stream')
        if (csrfToken) initXhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

        initXhr.addEventListener('load', () => {
            if (isAborted()) return
            if (initXhr.status !== 200) return onError(extractErrorBody(initXhr))

            let { partSize, numParts, signPartUrl, completeUrl, abortUrl } = JSON.parse(initXhr.responseText)
            abortUrls.add(abortUrl)

            let bytesUploaded = 0
            let partIndex = 0
            let retryCount = 0

            let signAndUploadNext = () => {
                if (isAborted()) return

                if (partIndex >= numParts) {
                    completeUpload()
                    return
                }

                let partNumber = partIndex + 1
                let start = partIndex * partSize
                let end = Math.min(start + partSize, file.size)
                let body = file.slice(start, end)

                let signXhr = new XMLHttpRequest()
                setCurrentXhr(signXhr)
                signXhr.open('GET', signPartUrl + (signPartUrl.includes('?') ? '&' : '?') + 'partNumber=' + partNumber)
                signXhr.setRequestHeader('Accept', 'application/json')
                if (csrfToken) signXhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

                signXhr.addEventListener('load', () => {
                    if (isAborted()) return
                    if (signXhr.status !== 200) return retryOrFail()

                    let { url } = JSON.parse(signXhr.responseText)
                    putPart(url, body)
                })
                signXhr.addEventListener('error', () => isAborted() || retryOrFail())
                signXhr.send()
            }

            let putPart = (signedUrl, body) => {
                let putXhr = new XMLHttpRequest()
                setCurrentXhr(putXhr)
                putXhr.open('PUT', signedUrl)

                putXhr.upload.addEventListener('progress', (e) => {
                    if (!e.lengthComputable) return
                    onProgress(bytesUploaded + e.loaded)
                })

                putXhr.addEventListener('load', () => {
                    if (isAborted()) return

                    if (putXhr.status === 200) {
                        bytesUploaded += body.size
                        partIndex++
                        retryCount = 0
                        signAndUploadNext()
                    } else if (putXhr.status === 403) {
                        if (retryCount < 1) { retryCount++; signAndUploadNext() }
                        else onError(extractErrorBody(putXhr))
                    } else {
                        retryOrFail()
                    }
                })

                putXhr.addEventListener('error', () => isAborted() || retryOrFail())
                putXhr.send(body)
            }

            let completeUpload = () => {
                let xhr = new XMLHttpRequest()
                setCurrentXhr(xhr)
                xhr.open('POST', completeUrl)
                xhr.setRequestHeader('Accept', 'application/json')
                if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

                xhr.addEventListener('load', () => {
                    if (xhr.status !== 200) return onError(extractErrorBody(xhr))

                    let { path } = JSON.parse(xhr.responseText)
                    onProgress(file.size)
                    onComplete(path)
                })
                xhr.addEventListener('error', () => isAborted() || onError(null))
                xhr.send()
            }

            let retryOrFail = () => {
                if (retryCount < retryDelays.length) {
                    let delay = retryDelays[retryCount++]
                    setTimeout(() => isAborted() || signAndUploadNext(), delay)
                } else {
                    onError(null)
                }
            }

            signAndUploadNext()
        })

        initXhr.addEventListener('error', () => isAborted() || onError(null))
        initXhr.send()
    }

    // Shared sequential-upload loop used by both the S3 direct-PUT,
    // S3 multipart, and local-chunked handlers.
    processFilesSequentially(name, uploadFile) {
        let uploadObj = this.uploadBag.first(name)
        let component = this.component
        let files = Array.from(uploadObj.files)
        let totalAllFiles = files.reduce((sum, f) => sum + f.size, 0)
        let bytesAlreadySentForCompletedFiles = 0
        let signedPaths = []
        let fileIndex = 0
        let aborted = false
        let currentXhr = null

        uploadObj.request = {
            abort: () => {
                aborted = true
                if (currentXhr) currentXhr.abort()
            },
        }

        let processNextFile = () => {
            if (aborted) return

            if (fileIndex >= files.length) {
                component.$wire.call('_finishUpload', name, signedPaths, uploadObj.multiple, uploadObj.append)
                return
            }

            let file = files[fileIndex]

            uploadFile(file, fileIndex, {
                onProgress: (bytesForThisFile) => {
                    let totalSent = bytesAlreadySentForCompletedFiles + bytesForThisFile
                    uploadObj.progressCallback({ loaded: totalSent, total: totalAllFiles })
                },
                onComplete: (signedPath) => {
                    signedPaths.push(signedPath)
                    bytesAlreadySentForCompletedFiles += file.size
                    fileIndex++
                    processNextFile()
                },
                onError: (errorBody) => {
                    component.$wire.call('_uploadErrored', name, errorBody, uploadObj.multiple)
                },
                isAborted: () => aborted,
                setCurrentXhr: (xhr) => { currentXhr = xhr },
            })
        }

        processNextFile()
    }

    uploadSingleChunked(file, name, chunkConfig, csrfToken, callbacks) {
        let { chunkSize, retryDelays, initUrl } = chunkConfig
        let { onProgress, onComplete, onError, isAborted, setCurrentXhr } = callbacks

        let totalSize = file.size
        let offset = 0
        let patchUrl = null
        let offsetUrl = null
        let retryCount = 0

        // Step 1: POST to init endpoint to get transfer ID and signed URLs
        let initXhr = new XMLHttpRequest()
        setCurrentXhr(initXhr)
        initXhr.open('POST', initUrl)
        initXhr.setRequestHeader('Accept', 'application/json')
        initXhr.setRequestHeader('Upload-Length', totalSize)
        initXhr.setRequestHeader('Upload-Name', encodeFilename(file.name))
        if (csrfToken) initXhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

        initXhr.addEventListener('load', () => {
            if (isAborted()) return

            if (initXhr.status !== 200) {
                onError(extractErrorBody(initXhr))
                return
            }

            try {
                let response = JSON.parse(initXhr.responseText)
                patchUrl = response.patchUrl
                offsetUrl = response.offsetUrl
                sendNextChunk()
            } catch (e) {
                onError(null)
            }
        })

        initXhr.addEventListener('error', () => {
            if (!isAborted()) onError(null)
        })

        initXhr.send()

        let sendNextChunk = () => {
            if (isAborted()) return
            if (offset >= totalSize) return

            let end = Math.min(offset + chunkSize, totalSize)
            let chunk = file.slice(offset, end)

            let xhr = new XMLHttpRequest()
            setCurrentXhr(xhr)
            xhr.open('PATCH', patchUrl)
            xhr.setRequestHeader('Accept', 'application/json')
            xhr.setRequestHeader('Content-Type', 'application/offset+octet-stream')
            xhr.setRequestHeader('Upload-Offset', offset)
            if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

            // Per-chunk XHR progress → overall progress for this file
            xhr.upload.addEventListener('progress', (e) => {
                if (!e.lengthComputable) return
                onProgress(offset + e.loaded)
            })

            xhr.addEventListener('load', () => {
                if (isAborted()) return

                if (xhr.status === 204) {
                    let newOffset = parseInt(xhr.getResponseHeader('Upload-Offset'))
                    offset = newOffset
                    retryCount = 0

                    if (xhr.getResponseHeader('Upload-Complete') === 'true') {
                        let signedPath = xhr.getResponseHeader('X-Signed-Path')
                        onProgress(totalSize)
                        onComplete(signedPath)
                        return
                    }

                    sendNextChunk()
                } else if (xhr.status === 409) {
                    // Offset mismatch — fetch the authoritative offset and retry
                    checkOffset(() => sendNextChunk(), () => onError(null))
                } else if (xhr.status === 422 || xhr.status === 413) {
                    // Validation failure or size limit — these are not retryable
                    onError(extractErrorBody(xhr))
                } else {
                    retryOrFail()
                }
            })

            xhr.addEventListener('error', () => {
                if (!isAborted()) retryOrFail()
            })

            xhr.send(chunk)
        }

        let checkOffset = (onSuccess, onFail) => {
            let xhr = new XMLHttpRequest()
            setCurrentXhr(xhr)
            xhr.open('GET', offsetUrl)
            xhr.setRequestHeader('Accept', 'application/json')
            if (csrfToken) xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken)

            xhr.addEventListener('load', () => {
                if (isAborted()) return

                if (xhr.status === 200) {
                    try {
                        let response = JSON.parse(xhr.responseText)
                        offset = response.offset
                        onSuccess()
                    } catch (e) {
                        onFail()
                    }
                } else {
                    onFail()
                }
            })

            xhr.addEventListener('error', () => {
                if (!isAborted()) onFail()
            })

            xhr.send()
        }

        let retryOrFail = () => {
            if (retryCount < retryDelays.length) {
                let delay = retryDelays[retryCount++]
                setTimeout(() => {
                    if (isAborted()) return
                    checkOffset(() => sendNextChunk(), () => onError(null))
                }, delay)
            } else {
                onError(null)
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
