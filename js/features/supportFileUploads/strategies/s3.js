import { sendRequest, withRetries } from '../request'

// Direct-to-S3 uploads. Every file gets its own presigned URL, so multiple
// file uploads work the same as single ones. Files over the chunk threshold
// arrive as an S3 multipart upload: each part is PUT to its own presigned
// URL, then the server completes the multipart upload (collecting ETags via
// ListParts, so no special CORS configuration is needed).
export default async function s3(ctx) {
    let { plan, files, uploadState, progress } = ctx

    let paths = []

    for (let [index, file] of files.entries()) {
        let entry = plan.files[index]

        if (entry.multipart) {
            paths.push(await uploadMultipart(entry.multipart, file, index, ctx))

            continue
        }

        if (uploadState.cancelled) throw { type: 'abort' }

        let headers = { ...entry.headers }

        if ('Host' in headers) delete headers.Host

        await withRetries(() => sendRequest({
            method: 'put',
            url: entry.url,
            headers,
            body: file,
            onProgress: loaded => progress.report(loaded),
            uploadState,
        }), { shouldRetry: error => error.type === 'network' })

        progress.commit(file.size)

        paths.push(entry.path)
    }

    return paths
}

async function uploadMultipart(multipart, file, index, ctx) {
    let { uploadState, progress, csrfHeaders, refreshPlan } = ctx

    // Parts counted toward progress so far (uploaded now or in a previous attempt)...
    let committed = new Set

    let commitPart = (partNumber, size) => {
        if (committed.has(partNumber)) return

        committed.add(partNumber)

        progress.commit(size)
    }

    Object.entries(multipart.uploadedParts || {}).forEach(([number, size]) => commitPart(+ number, size))

    let refreshes = 0

    while (true) {
        try {
            // The plan's part list is authoritative — the server only includes
            // parts S3 doesn't already have...
            for (let part of multipart.parts) {
                if (uploadState.cancelled) throw { type: 'abort' }

                let start = (part.partNumber - 1) * multipart.partSize
                let blob = file.slice(start, Math.min(start + multipart.partSize, file.size))

                await withRetries(() => sendRequest({
                    method: 'put',
                    url: part.url,
                    body: blob,
                    onProgress: loaded => progress.report(loaded),
                    uploadState,
                }), {
                    shouldRetry: error => error.type === 'network'
                        || (error.type === 'status' && error.status >= 500),
                })

                commitPart(part.partNumber, blob.size)
            }

            let response = await withRetries(() => sendRequest({
                method: 'post',
                url: multipart.completeUrl,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    ...csrfHeaders,
                },
                body: JSON.stringify({ action: 'complete', ref: multipart.ref }),
                uploadState,
            }), {
                shouldRetry: error => error.type === 'network'
                    || (error.type === 'status' && error.status >= 500),
            })

            return response.paths[0]
        } catch (error) {
            // The presigned part URLs (or the completion URL) expired mid-upload —
            // slow connections, big files. Re-handshake for a fresh plan and pick
            // up where we left off...
            let expired = error && error.type === 'status' && [401, 403].includes(error.status)

            if (! expired || ! refreshPlan || ++refreshes > 3) throw error

            let fresh = await refreshPlan()

            let entry = fresh.strategy === 's3' && fresh.files[index] && fresh.files[index].multipart

            if (! entry) throw error

            multipart = entry

            Object.entries(multipart.uploadedParts || {}).forEach(([number, size]) => commitPart(+ number, size))
        }
    }
}
