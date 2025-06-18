import requestManager from './requestManager.js'

export default class Request {
    controller = new AbortController()

    cancel() {
        this.controller.abort('cancelled')

        requestManager.remove(this)
    }

    isCancelled() {
        return this.controller.signal.aborted
    }

    cancelIfItShouldBeCancelled() {
        console.error('cancelIfItShouldBeCancelled must be implemented')
    }

    shouldCancel() {
        console.error('shouldCancel must be implemented')
    }

    async send() {
        console.error('send must be implemented')
    }
}