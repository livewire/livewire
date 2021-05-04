import { v4 as generateUniqueId } from 'uuid'
import { setUploadLoading, unsetUploadLoading } from './component/LoadingStates'
import { getCsrfToken } from '@/util'

export default class PendingUpload {
    constructor(manager, el, name, file) {
        this.manager = manager
        this.el = el
        this.name = name
        this.file = file
        this.id = generateUniqueId()
        this.request = null
    }

    fileInfo() {
        return {
            id: this.id,
            name: this.file.name,
            size: this.file.size,
            type: this.file.type,
            multiple: this.el.multiple,
        }
    }

    requestUpload() {
        setUploadLoading(this.manager.component, this.name)

        this.emit('start')

        this.manager.component.call('requestUpload', this.name, this.fileInfo())
    }

    emit(event, detail = {}) {
        this.el.dispatchEvent(
            new CustomEvent(
                `livewire-upload-${event}`,
                { bubbles: true, detail: { pendingUpload: this, ...detail } }
            )
        )
    }

    abort() {
        // Cannot abort the request if it doesn't exist, or is not processing still.
        if (!this.request || this.request?.status !== 0) return

        this.request?.abort()

        this.emit('abort')
    }

    startUpload(url) {
        setUploadLoading(this.manager.component, this.name)

        let formData = new FormData()
        formData.append('files[]', this.file)

        let headers = { 'Accept': 'application/json' }
        let csrfToken = getCsrfToken()
        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken

        this.request = this.makeRequest({
            url,
            headers,
            formData,
            method: 'post',

            success: response => {
                if (!response.paths.length) return // Should this throw an error?

                this.finishUpload(response.paths[0])
            },

            error: errors => { }, // TODO
        })
    }

    startS3Upload(payload) {
        setUploadLoading(this.manager.component, this.name)

        if ('Host' in payload.headers) delete payload.headers.Host

        this.request = this.makeRequest({
            url: payload.url,
            headers: payload.headers,
            formData: this.file,
            method: 'put',
            success: response => this.finishUpload(payload.path),
            error: errors => { }, // TODO
        })
    }

    finishUpload(path) {
        this.manager.component.call('finishUpload', this.name, this.fileInfo(), path)
    }

    markUploadFinished() {
        unsetUploadLoading(this.manager.component)

        this.emit('finish')
    }

    makeRequest({ formData, method, url, headers, success, error }) {
        let request = new XMLHttpRequest()

        request.open(method, url)

        Object.entries(headers).forEach(([key, value]) => {
            request.setRequestHeader(key, value)
        })

        request.upload.addEventListener('progress', event => {
            const progress = Math.round((event.loaded * 100) / event.total)

            this.emit('progress', { progress })
        })

        request.addEventListener('load', () => {
            if ((request.status + '')[0] === '2') {
                return success(request.response && JSON.parse(request.response))
            }

            let errors = null

            if (request.status === 422) {
                errors = request.response
            }

            error(errors)
        })

        request.send(formData)

        return request
    }
}
