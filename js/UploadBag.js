import { setUploadLoading, unsetUploadLoading } from './component/LoadingStates'

export default class UploadBag {
    constructor(component) {
        this.bag = {}
        this.component = component
    }

    fresh(name) {
        this.bag[name] = {
            __state: {
                size: 0,
                sent: 0,
                progress: 0,
                processing: false,
            }
        }
    }

    add(name, pendingUpload) {
        if (! this.has(name)) {
            this.fresh(name)
        }

        this.bag[name][pendingUpload.id] = pendingUpload
        this.bag[name].__state.size += pendingUpload.file.size
    }

    get(name, id = null) {
        if (id === null) return this.bag?.[name]

        return this.bag?.[name]?.[id]
    }

    has(name) {
        return name in this.bag
    }

    hasUploads(name) {
        return this.remaining(name) > 0
    }

    remove(name, id = null) {
        if (id === null) return delete this.bag?.[name]

        delete this.bag?.[name]?.[id]

        if (! this.hasUploads(name)) {
            delete this.bag?.[name]
        }
    }

    remaining(name) {
        return Object.keys(this.bag?.[name] || {})
            .filter(key => key !== '__state')
            .length
    }

    ensureLoadingStateIsSet(name) {
        if (this.hasUploads(name)) {
            this.bag[name].__state.processing = true
            setUploadLoading(this.component, name)
        }
    }

    started(name, el) {
        if (! this.has(name)) {
            this.fresh(name)
        }

        this.bag[name].__state.processing = true

        this.emit(el, name, 'start')
        setUploadLoading(this.component, name)
    }

    finished(name) {
        if (! this.has(name)) {
            this.fresh(name)
        }

        this.bag[name].__state.processing = false

        unsetUploadLoading(this.component)
    }

    progressed(el, name, bytesSent) {
        if (! this.has(name)) {
            this.fresh(name)
        }

        this.bag[name].__state.processing = true

        this.bag[name].__state.sent += bytesSent

        const progress = this.bag[name].__state.sent / this.bag[name].__state.size

        this.bag[name].__state.progress = progress

        this.emit(el, name, 'progress', { progress: Math.round(progress * 100) })
    }

    emit(el, name, event, detail = {}) {
        if (! this.has(name)) return

        el?.dispatchEvent(
            new CustomEvent(
                `livewire-upload-${event}`,
                { bubbles: true, detail }
            )
        )
    }
}
