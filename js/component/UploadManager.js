import PendingUpload from '../PendingUpload'
import UploadBag from '../UploadBag'

class UploadManager {
    constructor(component) {
        this.component = component
        this.uploadBag = new UploadBag
    }

    registerListeners() {
        this.component.on('upload:generatedSignedUrl', (name, fileInfo, url) => {
            this.uploadBag.get(name, fileInfo.id)
                .startUpload?.(url)
        })

        this.component.on('upload:generatedSignedUrlForS3', (name, fileInfo, payload) => {
            this.uploadBag.get(name, fileInfo.id)
                .startS3Upload?.(payload)
        })

        this.component.on('upload:finished', (name, fileInfo) => {
            this.uploadBag.get(name, fileInfo.id)
                .markUploadFinished?.()

            this.uploadBag.remove(name, fileInfo.id)
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
    }
}

export default UploadManager
