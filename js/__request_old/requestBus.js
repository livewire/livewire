class RequestBus {
    requests = new Set()

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