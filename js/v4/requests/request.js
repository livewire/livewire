import requestBus from './requestBus.js'

export default class Request {
    controller = new AbortController()

    respondCallbacks = []
    succeedCallbacks = []
    errorCallbacks = []

    cancel() {
        this.controller.abort('cancelled')
    }

    finish() {
        requestBus.remove(this)
    }

    isCancelled() {
        return this.controller.signal.aborted
    }

    async send() {
        console.error('send must be implemented')
    }

    addRespondCallback(callback) {
        this.respondCallbacks.push(callback)
    }

    addSucceedCallback(callback) {
        this.succeedCallbacks.push(callback)
    }

    addErrorCallback(callback) {
        this.errorCallbacks.push(callback)
    }
}
