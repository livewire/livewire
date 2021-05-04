export default class UploadBag {
    constructor() {
        this.bag = {}
    }

    add(name, pendingUpload) {
        if (!this.bag[name]) {
            this.bag[name] = {}
        }

        this.bag[name][pendingUpload.id] = pendingUpload
    }

    get(name, id = null) {
        if (id === null) return this.bag?.[name]

        return this.bag?.[name]?.[id]
    }

    remove(name, id = null) {
        if (id === null) return delete this.bag?.[name]

        delete this.bag?.[name]?.[id]

        if (!Object.keys(this.bag?.[name] || {}).length) {
            delete this.bag?.[name]
        }
    }
}
