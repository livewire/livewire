import store from '@/Store'

function isFileInput(el) {
    return el.tagName.toLowerCase() === 'input'
        && el.type === 'file'
}

export default function () {
    store.registerHook('interceptWireModelAttachListener', (directive, el, component) => {
        if (!isFileInput(el)) return

        let eventHandler = event => {
            if (event.target.files.length === 0) return

            // TODO Handle wire:model.defer?

            component.upload(directive.value, event)
        }

        el.addEventListener('change', eventHandler)

        // There's a bug in browsers where selecting a file, removing it,
        // then re-adding it doesn't fire the change event. This fixes it.
        // Reference: https://stackoverflow.com/questions/12030686/html-input-file-selection-event-not-firing-upon-selecting-the-same-file
        let clearFileInputValue = () => { el.value = null }
        el.addEventListener('click', clearFileInputValue)

        component.addListenerForTeardown(() => {
            el.removeEventListener('change', eventHandler)
            el.removeEventListener('click', clearFileInputValue)
        })
    })
}
