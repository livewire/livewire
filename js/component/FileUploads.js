import { getCsrfToken } from '@/util'
import store from '@/Store'
import { setUploadLoading, unsetUploadLoading } from './LoadingStates'

export default function () {
    store.registerHook('interceptWireModelAttachListener', (el, directive, component) => {
        if (! (el.rawNode().tagName.toLowerCase() === 'input' && el.rawNode().type === 'file')) return

        let eventHandler = e => {
            let conformToFileInfoObject = file => {
                return { name: file.name, size: file.size, type: file.type }
            }

            let fileInfos = Array.from(e.target.files).map(conformToFileInfoObject)
            let isMultiple = !! e.target.multiple
            let modelName = directive.value

            component.call('startUpload', modelName, fileInfos, isMultiple);

            markUploadStarted(component, el, directive)
        }

        el.addEventListener('change', eventHandler)

        component.addListenerForTeardown(() => {
            el.removeEventListener('change', eventHandler)
        })

        component.on('file-upload:generatedSignedUrl', url => {
            // We have to add reduntant "setLoading" calls because the dom-patch
            // from the first response will clear the setUploadLoading call
            // from the first upload call.
            setUploadLoading(component, directive.value)

            handleSignedUrl(url, component, el, directive)
        })
        component.on('file-upload:generatedSignedUrlForS3', payload => {
            setUploadLoading(component, directive.value)

            handleS3PreSignedUrl(payload, component, el, directive)
        })
        component.on('file-upload:finished', () => markUploadFinished(component, el))
        component.on('file-upload:errored', () => markUploadErrored(component, el))
    })
}

function handleSignedUrl(url, component, el, directive) {
    let formData = new FormData()
    Array.from(el.rawNode().files).forEach(file => formData.append('files[]', file))

    let headers = {
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json',
    }

    makeRequest(component, el, directive, formData, 'post', url, headers, response => {
        return response.paths
    })
}

function handleS3PreSignedUrl(payload, component, el, directive) {
    let formData = el.rawNode().files[0]

    let headers = payload.headers
    if ('Host' in headers) delete headers.Host
    let url = payload.url

    makeRequest(component, el, directive, formData, 'put', url, headers, response => {
        return [payload.path]
    })
}

function makeRequest(component, el, directive, formData, method, url, headers, retrievePaths) {
    let request = new XMLHttpRequest()
    request.open(method, url)

    Object.entries(headers).forEach(([key, value]) => {
        request.setRequestHeader(key, value)
    })

    request.upload.addEventListener('progress', handleUploadProgress(el))

    request.addEventListener('load', () => {
        if ((request.status+'')[0] === '2') {
            let paths = retrievePaths(request.response && JSON.parse(request.response))

            component.call('finishUpload', directive.value, paths, !! el.rawNode().multiple)

            return
        }

        let errors = null

        if (request.status === 422) {
            errors = request.response
        }

        component.call('uploadErrored', directive.value, errors, !! el.rawNode().multiple)
    })

    request.send(formData)
}

function handleUploadProgress(el) {
    return (progressEvent) => {
        var percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total )

        el.rawNode().dispatchEvent(
            new CustomEvent('livewire-upload-progress', {
                bubbles: true, detail: { progress: percentCompleted }
            })
        )
    }
}

function markUploadStarted(component, el, directive) {
    setUploadLoading(component, directive.value)

    el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-started', { bubbles: true }))
}

function markUploadFinished(component, el) {
    unsetUploadLoading(component)

    el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-finished', { bubbles: true }))
}

function markUploadErrored(component, el) {
    unsetUploadLoading(component)

    el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-error', { bubbles: true }))
}
