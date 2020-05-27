import { getCsrfToken } from '@/util'
import store from '@/Store'

export default function () {
    store.registerHook('interceptWireModelAttachListener', (el, directive, component, debounceIf) => {
        if (! (el.rawNode().tagName.toLowerCase() === 'input' && el.rawNode().type === 'file')) return

        let eventHandler = e => {
            let conformFileToFileInfoObject = file => {
                return { name: file.name, size: file.size, type: file.type }
            }

            let fileInfo = Array.from(e.target.files).map(conformFileToFileInfoObject)

            component.call('generateSignedRoute', directive.value, fileInfo, !! e.target.multiple);
        }

        el.addEventListener('change', eventHandler)

        component.addListenerForTeardown(() => {
            el.removeEventListener('change', eventHandler)
        })

        component.on('generatedPreSignedS3Url', payload => handleS3PreSignedUrl(payload, component, el, directive))
        component.on('generatedSignedUrl', url => handleSignedUrl(url, component, el, directive))
    })
}

function handleS3PreSignedUrl(payload, component, el, directive)
{
    let headers = payload.headers;

    if ('Host' in headers) {
        delete headers.Host;
    }

    axios.put(payload.url, el.rawNode().files[0], {
        cancelToken: '',
        headers: headers,
        onUploadProgress: (progressEvent) => {
            var percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total )

            el.rawNode().dispatchEvent(
                new CustomEvent('livewire-upload-progress', {
                    bubbles: true, detail: { progress: percentCompleted }
                })
            )
        }
    }).then(response => {
        component.call('finishUpload', directive.value, [payload.path], el.rawNode().multiple)
        el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-finished', { bubbles: true }))
    }).catch(error => {
        el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-error', { bubbles: true }))
        // @todo: handle main endpoint validation.
        // if (error.response.status === 422) {
        //     error.response.data.errors
        // }
    })

    // response.data.extension = file.name.split('.').pop()
}

function handleSignedUrl(url, component, el, directive)
{
    el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-started', { bubbles: true }))

    let model = directive.value
    let files = el.rawNode().files
    let formData = new FormData()

    Array.from(files).forEach(file => formData.append('files[]', file))

    axios.post(url, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        onUploadProgress(progressEvent) {
            var percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total )

            el.rawNode().dispatchEvent(
                new CustomEvent('livewire-upload-progress', {
                    bubbles: true, detail: { progress: percentCompleted }
                })
            )
        },
    })
    .then(function (response) {
        component.call('finishUpload', model, response.data.paths, el.rawNode().multiple)
        el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-finished', { bubbles: true }))
    })
    .catch(function (error) {
        el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-error', { bubbles: true }))
        // @todo: handle main endpoint validation.
        if (error.response.status === 422) {
            error.response.data.errors
        }
    });
}
