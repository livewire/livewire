import { setUploadLoading, unsetUploadLoading } from './LoadingStates'
import { getCsrfToken } from '@/util'

class UploadManager {
    constructor(component) {
        this.component = component
        this.uploadBag = {}
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

        this.component.on('upload:finished', (name) => this.markUploadFinished(name))
        this.component.on('upload:errored', (name) => this.markUploadErrored(name))
    }

    find(name) {
        return this.uploadBag[name]
    }

    clear(name) {
        delete this.uploadBag[name]
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
            files,
            multiple: true,
            finishCallback,
            errorCallback,
            progressCallback,
        })
    }

    setUpload(name, uploadObject) {
        this.uploadBag[name] = uploadObject

        let conformToFileInfoObject = file => {
            return { name: file.name, size: file.size, type: file.type }
        }

        let fileInfos = uploadObject.files.map(conformToFileInfoObject)

        this.component.call('startUpload', name, fileInfos, uploadObject.multiple);

        this.markUploadStarted(name)
    }

    handleSignedUrl(name, url) {
        let formData = new FormData()
        Array.from(this.find(name).files).forEach(file => formData.append('files[]', file))

        let headers = {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        }

        this.makeRequest(name, formData, 'post', url, headers, response => {
            return response.paths
        })
    }

    handleS3PreSignedUrl(name, payload) {
        let formData = this.find(name).files[0]

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

        request.upload.addEventListener('progress', this.find(name).progressCallback)

        request.addEventListener('load', () => {
            if ((request.status+'')[0] === '2') {
                let paths = retrievePaths(request.response && JSON.parse(request.response))

                this.component.call('finishUpload', name, paths, this.find(name).multiple)

                return
            }

            let errors = null

            if (request.status === 422) {
                errors = request.response
            }

            this.component.call('uploadErrored', name, errors, this.find(name).multiple)
        })

        request.send(formData)
    }

    markUploadStarted(name) {
        setUploadLoading(this.component, name)
    }

    markUploadFinished(name) {
        unsetUploadLoading(this.component)

        this.find(name).finishCallback()
        this.clear(name)
    }

    markUploadErrored(name) {
        unsetUploadLoading(this.component)

        this.find(name).errorCallback()
        this.clear(name)
    }
}

export default UploadManager
