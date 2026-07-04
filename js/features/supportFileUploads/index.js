import { getUploadManager, MessageBag } from './manager'

export function handleFileUpload(el, property, component, cleanup) {
    let manager = getUploadManager(component)

    let start = () => el.dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true, detail: { id: component.id, property } }))
    let finish = () => el.dispatchEvent(new CustomEvent('livewire-upload-finish', { bubbles: true, detail: { id: component.id, property } }))
    let error = () => el.dispatchEvent(new CustomEvent('livewire-upload-error', { bubbles: true, detail: { id: component.id, property } }))
    let cancel = () => el.dispatchEvent(new CustomEvent('livewire-upload-cancel', { bubbles: true, detail: { id: component.id, property } }))
    let progress = (progressEvent) => {
        let detail = progressEvent.detail || {}

        el.dispatchEvent(
            new CustomEvent('livewire-upload-progress', {
                bubbles: true,
                detail: {
                    id: component.id,
                    property,
                    progress: detail.progress ?? Math.round((progressEvent.loaded * 100) / progressEvent.total),
                    loaded: progressEvent.loaded,
                    total: progressEvent.total,
                },
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

export default MessageBag

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
