import { setUploadLoading, unsetUploadLoading } from './LoadingStates'
import { getCsrfToken } from '@/util'
import MessageBag from '../MessageBag'

class UploadManager {
    constructor(component) {
        this.component = component
        this.uploadBag = new MessageBag
        this.removeBag = new MessageBag
    }

    registerListeners() {
        this.component.on('upload:generatedSignedUrl', (name, url) => {
            // We have to add reduntant "setLoading" calls because the dom-patch
            // from the first response will clear the setUploadLoading call
            // from the first upload call.
            setUploadLoading(this.component, name)

            this.handleSignedUrl(name, url)
        })

        this.component.on('upload:generatedSignedUrlForS3', (name, payload) => {
            setUploadLoading(this.component, name)

            this.handleS3PreSignedUrl(name, payload)
        })

        this.component.on('upload:finished', (name, tmpFilenames) => this.markUploadFinished(name, tmpFilenames))
        this.component.on('upload:errored', (name) => this.markUploadErrored(name))
        this.component.on('upload:removed', (name, tmpFilename) => this.removeBag.shift(name).finishCallback(tmpFilename))
    }

    upload(name, file, finishCallback, errorCallback, progressCallback) {
        this.setUpload(name, {
            files: [file],
            multiple: false,
            finishCallback,
            errorCallback,
            progressCallback,
        })
    }

    uploadMultiple(name, files, finishCallback, errorCallback, progressCallback) {
        this.setUpload(name, {
            files: Array.from(files),
            multiple: true,
            finishCallback,
            errorCallback,
            progressCallback,
        })
    }

    removeUpload(name, tmpFilename, finishCallback) {
        this.removeBag.push(name, {
            tmpFilename, finishCallback
        })

        this.component.call('removeUpload', name, tmpFilename);
    }

    setUpload(name, uploadObject) {
        this.uploadBag.add(name, uploadObject)

        if (this.uploadBag.get(name).length === 1) {
            this.startUpload(name, uploadObject)
        }
    }

    handleSignedUrl(name, url) {
        let formData = new FormData()
        Array.from(this.uploadBag.first(name).files).forEach(file => formData.append('files[]', file))

        let headers = {
            'Accept': 'application/json',
        }

        let csrfToken = getCsrfToken()

        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken

        let promise = this.makeRequest(name, formData, 'post', url, headers,)

        promise.then(result => {
            this.component.call('finishUpload', name, result.response.paths, this.uploadBag.first(name).multiple)
        }).catch(error => {
            this.component.call('uploadErrored', name, error, this.uploadBag.first(name).multiple)
        })
    }

    handleS3PreSignedUrl(name, payloads) {
        let promises = []
        payloads.forEach((payload, index) => {
            let formData = this.uploadBag.first(name).files[index]

            let headers = payload.headers
            if ('Host' in headers) delete headers.Host
            let url = payload.url

            promises.push(this.makeRequest(name, formData, 'put', url, headers))
        });

        Promise.all(promises)
            .then(results => {
                let fulfilled = []

                results.forEach(response => {
                    fulfilled.push(response.url)
                })

                let fulfilledPaths = payloads.filter(payload => fulfilled.includes(payload.url)).map(payload => payload.path)

                this.component.call('finishUpload', name, fulfilledPaths, this.uploadBag.first(name).multiple)

            }).catch(error => {
                this.component.call('uploadErrored', name, error, this.uploadBag.first(name).multiple)
            })

    }

    makeRequest(name, formData, method, url, headers) {

        return new Promise((resolve, reject) => {
            let request = new XMLHttpRequest()
            request.open(method, url)

            Object.entries(headers).forEach(([key, value]) => {
                request.setRequestHeader(key, value)
            })

            request.upload.addEventListener('progress', e => {
                e.detail = {}
                e.detail.progress = Math.round((e.loaded * 100) / e.total)

                this.uploadBag.first(name).progressCallback(e)
            })

            request.addEventListener('load', () => {
                if ((request.status + '')[0] === '2') {
                    resolve({ response: request.response && JSON.parse(request.response), url: url })

                    return
                }

                let errors

                if (request.status === 422) {
                    errors = request.response
                }

                reject(errors ?? {})
            })
            request.send(formData)
        })
    }

    startUpload(name, uploadObject) {
        let fileInfos = uploadObject.files.map(file => {
            return { name: file.name, size: file.size, type: file.type }
        })

        this.component.call('startUpload', name, fileInfos, uploadObject.multiple);

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
}

export default UploadManager
