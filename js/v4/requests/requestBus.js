class RequestBus {
    booted = false
    requests = new Set()

    boot() {
        this.booted = true

        console.log('v4 requests enabled')
    }

    add(request) {
        this.cancelRequestsThatShouldBeCancelled(request.shouldCancel())

        this.requests.add(request)

        request.send()
    }

    remove(request) {
        this.requests.delete(request)
    }

    cancelRequestsThatShouldBeCancelled(shouldCancel) {
        this.requests.forEach(request => {
            if (shouldCancel(request)) {
                request.cancel()
            }
        })
    }
}

let instance = new RequestBus()

export default instance