class RequestManager {
    requests = new Set()

    add(request) {
        this.requests.add(request)

        request.send()
    }

    remove(request) {
        this.requests.delete(request)
    }
}

let instance = new RequestManager()

export default instance