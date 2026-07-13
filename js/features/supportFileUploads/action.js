import { getUploadManager } from './manager'
import { dataGet } from '@/utils'

// The smart `$upload` action behind `$wire.$upload()` and template
// expressions like `wire:paste="$upload('photos')"`. Files are acquired
// from the first available source:
//
//   1. An explicit argument (a File, FileList, array of Files, or an Event)
//   2. The DOM event currently being dispatched (pasted files, dropped
//      files, or a file input's selection)
//   3. The user — via the browser's file picker
//
// A file-capable event that carries no files (a plain text paste, a text
// drag) is a no-op, so it never hijacks the browser's default behavior...
export function uploadAction(component, name, ...args) {
    let { files, event, options, callbacks } = parseArguments(args)

    let promise

    if (! files && ! event) {
        // No explicit source — inherit the event currently being
        // dispatched, if there is one...
        event = window.event

        files = filesFromEvent(event)
    }

    if (files) {
        // Suppress the event's default behavior only when we're actually
        // consuming its files — a text paste must proceed untouched...
        if (event && files.length > 0) event.preventDefault()

        promise = uploadFiles(component, name, files, options, callbacks)
    } else {
        promise = openFilePicker(component, name, options).then(pickedFiles => {
            if (pickedFiles.length === 0) {
                callbacks.cancelled()

                return null
            }

            return uploadFiles(component, name, pickedFiles, options, callbacks)
        })
    }

    return markAsAction(promise)
}

function parseArguments(args) {
    let files = null
    let event = null
    let options = {}
    let callbacks = {}

    let [second, ...rest] = args

    if (second instanceof Event) {
        event = second

        files = filesFromEvent(second) || []
    } else if (typeof File !== 'undefined' && second instanceof File) {
        files = [second]
    } else if (typeof FileList !== 'undefined' && second instanceof FileList) {
        files = Array.from(second)
    } else if (Array.isArray(second)) {
        files = second
    } else if (isPlainObject(second)) {
        options = second
    }

    // Legacy signature: $upload(name, file, finish, error, progress, cancelled)...
    if (typeof rest[0] === 'function') {
        let [finish, error, progress, cancelled] = rest

        callbacks = { finish, error, progress, cancelled }
    } else if (isPlainObject(rest[0])) {
        options = rest[0]
    }

    callbacks.finish ??= () => {}
    callbacks.error ??= () => {}
    callbacks.progress ??= () => {}
    callbacks.cancelled ??= () => {}

    return { files, event, options, callbacks }
}

// Pull files out of a paste, drop, or file-input change event. Returns
// null for events that can't carry files (like clicks), so callers can
// fall back to opening the file picker...
function filesFromEvent(event) {
    if (! event) return null

    let source = event.clipboardData || event.dataTransfer || (event.target?.files ? event.target : null)

    if (! source) return null

    let files = Array.from(source.files || [])

    return files
}

function uploadFiles(component, name, files, options, callbacks) {
    let manager = getUploadManager(component)

    let multiple = options.multiple ?? Array.isArray(dataGet(component.ephemeral, name))

    if (options.accept) {
        files = files.filter(file => matchesAccept(file, options.accept))
    }

    if (files.length === 0) return Promise.resolve(null)

    if (! multiple) files = files.slice(0, 1)

    return new Promise((resolve, reject) => {
        let finish = result => { callbacks.finish(result); resolve(uploadedObjects(component, name, multiple, result)) }
        let error = () => { callbacks.error(); reject(new Error(`Livewire upload to [${name}] failed`)) }
        let cancelled = () => { callbacks.cancelled(); resolve(null) }

        if (multiple) {
            manager.uploadMultiple(name, files, finish, error, callbacks.progress, cancelled, options.append ?? true)
        } else {
            manager.upload(name, files[0], finish, error, callbacks.progress, cancelled)
        }
    })
}

// Resolve the promise with the reactive rich upload object(s) now living
// on the property, mirroring the upload's shape: a single upload resolves
// one object, a multiple upload resolves an array holding just this
// batch (not the property's previously uploaded files)...
function uploadedObjects(component, name, multiple, tmpFilenames) {
    let value = dataGet(component.reactive, name)

    if (! multiple) return value

    let filenames = Array.isArray(tmpFilenames) ? tmpFilenames : [tmpFilenames]

    return (value || []).filter(upload => filenames.includes(upload?.filename))
}

// Open the browser's file picker and resolve with the chosen files
// (or an empty array if the dialog is dismissed)...
function openFilePicker(component, name, options) {
    return new Promise(resolve => {
        let input = document.createElement('input')

        input.type = 'file'
        input.style.display = 'none'
        input.setAttribute('data-livewire-picker', name)

        if (options.accept) input.accept = options.accept
        if (options.multiple ?? Array.isArray(dataGet(component.ephemeral, name))) input.multiple = true

        let settle = files => { input.remove(); resolve(files) }

        input.addEventListener('change', () => settle(Array.from(input.files)))
        input.addEventListener('cancel', () => settle([]))

        // Some browsers require the input to be in the document for the
        // picker to open...
        document.body.appendChild(input)

        input.click()
    })
}

// Match a file against an `accept` string the same way a native file
// input does: comma-separated mime types (with wildcards) or extensions...
function matchesAccept(file, accept) {
    let patterns = accept.split(',').map(pattern => pattern.trim().toLowerCase()).filter(Boolean)

    if (patterns.length === 0) return true

    let type = (file.type || '').toLowerCase()
    let extension = '.' + file.name.split('.').pop().toLowerCase()

    return patterns.some(pattern => {
        if (pattern.startsWith('.')) return extension === pattern

        if (pattern.endsWith('/*')) return type.startsWith(pattern.slice(0, -1))

        return type === pattern
    })
}

function isPlainObject(value) {
    return typeof value === 'object' && value !== null && value.constructor === Object
}

// Flag the promise so action expressions silence rejections (upload
// failures surface as validation errors on the component), while JS
// callers who await the promise still get them...
function markAsAction(promise) {
    promise._livewireAction = true

    promise.catch(() => {})

    return promise
}
