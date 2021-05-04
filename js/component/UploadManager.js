import PendingUpload from '../PendingUpload'
import UploadBag from '../UploadBag'

class UploadManager {
    constructor(component) {
        this.component = component
        this.uploadBag = new UploadBag(component)
    }

    registerListeners() {
        this.component.on('upload:generatedSignedUrl', (name, fileInfo, url) => {
            this.uploadBag.ensureLoadingStateIsSet(name)

            this.uploadBag.get(name, fileInfo.id)
                .startUpload?.(url)
        })

        this.component.on('upload:generatedSignedUrlForS3', (name, fileInfo, payload) => {
            this.uploadBag.ensureLoadingStateIsSet(name)

            this.uploadBag.get(name, fileInfo.id)
                .startS3Upload?.(payload)
        })

        this.component.on('upload:finished', (name, fileInfo) => {
            this.uploadBag.ensureLoadingStateIsSet(name)

            const isLastOne = this.uploadBag.remaining(name) === 1

            this.uploadBag.get(name, fileInfo.id)
                .markUploadFinished?.(isLastOne)

            this.uploadBag.remove(name, fileInfo.id)

            if (! this.uploadBag.hasUploads(name)) {
                this.uploadBag.finished(name)
            }
        })

        // TODO Implement these in PendingUpload as well
        // this.component.on('upload:errored', (name) => this.markUploadErrored(name))
        // this.component.on('upload:removed', (name, tmpFilename) => this.removeBag.shift(name).finishCallback(tmpFilename))
    }

    handle(name, event) {
        Array.from(event.target.files)
            .map(file => new PendingUpload(this, event.target, name, file))
            .forEach(item => {
                this.uploadBag.add(name, item)

                item.requestUpload()
            })

        this.uploadBag.started(name, event.target)
    }
}

export default UploadManager
