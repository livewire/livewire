class RequestBus {
    booted = false
    requests = new Set()

    boot() {
        this.booted = true

        console.log('v4 requests enabled')
    }

    add(request) {
        this.cancelRequestsThatShouldBeCancelled(request)

        this.requests.add(request)

        request.send()
    }

    remove(request) {
        this.requests.delete(request)
    }

    cancelRequestsThatShouldBeCancelled(newRequest) {
        this.requests.forEach(existingRequest => {
            newRequest.processCancellations(existingRequest)
        })
    }
}

let instance = new RequestBus()

export default instance