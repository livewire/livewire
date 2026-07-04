export function sendRequest({ method, url, headers = {}, body = null, onProgress = null, uploadState = null }) {
    return new Promise((resolve, reject) => {
        let request = new XMLHttpRequest()

        request.open(method, url)

        Object.entries(headers).forEach(([key, value]) => {
            request.setRequestHeader(key, value)
        })

        if (onProgress) {
            request.upload.addEventListener('progress', e => onProgress(e.loaded, e.total))
        }

        request.addEventListener('load', () => {
            let response = null

            try {
                response = request.response && JSON.parse(request.response)
            } catch (e) {}

            if ((request.status + '')[0] === '2') return resolve(response)

            reject({ type: 'status', status: request.status, response, raw: request.response })
        })

        request.addEventListener('error', () => reject({ type: 'network' }))
        request.addEventListener('abort', () => reject({ type: 'abort' }))

        // Expose the in-flight request so cancelUpload() can abort it...
        if (uploadState) uploadState.request = request

        request.send(body)
    })
}

export async function withRetries(makeAttempt, { retries = 2, shouldRetry = () => true } = {}) {
    let attempt = 0

    while (true) {
        try {
            return await makeAttempt()
        } catch (error) {
            if (error && error.type === 'abort') throw error

            if (attempt >= retries || ! shouldRetry(error)) throw error

            attempt++

            await new Promise(resolve => setTimeout(resolve, 500 * attempt))
        }
    }
}
