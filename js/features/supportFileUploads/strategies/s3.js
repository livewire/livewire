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
            paths.push(await uploadMultipart(entry.multipart, file, ctx))

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

async function uploadMultipart(multipart, file, ctx) {
    let { uploadState, progress, csrfHeaders } = ctx

    // Parts already uploaded in a previous attempt count toward progress...
    Object.values(multipart.uploadedParts || {}).forEach(size => progress.commit(size))

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

        progress.commit(blob.size)
    }

    let response = await sendRequest({
        method: 'post',
        url: multipart.completeUrl,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...csrfHeaders,
        },
        body: JSON.stringify({ action: 'complete', ref: multipart.ref }),
        uploadState,
    })

    return response.paths[0]
}
