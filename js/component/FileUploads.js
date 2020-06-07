import store from '@/Store'

export default function () {
    store.registerHook('interceptWireModelAttachListener', (el, directive, component) => {
        if (! (el.rawNode().tagName.toLowerCase() === 'input' && el.rawNode().type === 'file')) return

        let start = () => el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-start', { bubbles: true }))
        let finish = () => el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-finish', { bubbles: true }))
        let error = () => el.rawNode().dispatchEvent(new CustomEvent('livewire-upload-error', { bubbles: true }))
        let progress = (progressEvent) => {
            var percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total )

            el.rawNode().dispatchEvent(
                new CustomEvent('livewire-upload-progress', {
                    bubbles: true, detail: { progress: percentCompleted }
                })
            )
        }

        let eventHandler = e => {
            if (e.target.files.length === 0) return

            start()

            if (e.target.multiple) {
                component.uploadMultiple(directive.value, e.target.files, finish, error, progress)
            } else {
                component.upload(directive.value, e.target.files[0], finish, error, progress)
            }
        }

        el.addEventListener('change', eventHandler)

        component.addListenerForTeardown(() => {
            el.removeEventListener('change', eventHandler)
        })
    })
}
