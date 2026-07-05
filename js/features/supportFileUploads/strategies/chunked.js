import { sendRequest, withRetries } from '../request'

// Large files on non-S3 disks: slice each file into chunks and POST them one
// at a time to the signed chunk endpoint. The server stitches the file back
// together when the last chunk lands.
//
// Resumable: the plan reports chunks that already made it to the server from
// a previous attempt, and those are skipped entirely.
export default async function chunked(ctx) {
    let { plan, files, uploadState, progress } = ctx

    let paths = []

    for (let [index, file] of files.entries()) {
        paths.push(await uploadFileInChunks(plan.files[index], index, file, ctx))
    }

    return paths
}

async function uploadFileInChunks(entry, index, file, ctx) {
    let { plan, headers, uploadState, progress } = ctx

    let chunkSize = entry.chunkSize
    let received = new Set(entry.receivedChunks)

    // If every chunk is already on the server but the file was never
    // assembled (a lost completion response), resend the last chunk to
    // trigger assembly...
    if (received.size >= entry.totalChunks) received.delete(entry.totalChunks - 1)

    // Bytes already on the server count toward progress...
    for (let i of received) progress.commit(chunkLength(file, i, chunkSize))

    let response = null
    let rounds = 0

    while (true) {
        let pending = allChunks(entry.totalChunks).filter(i => ! received.has(i))

        for (let i of pending) {
            if (uploadState.cancelled) throw { type: 'abort' }

            response = await sendChunk(entry, index, i, file, chunkSize, ctx)

            progress.commit(chunkLength(file, i, chunkSize))

            received.add(i)
        }

        if (response && response.complete) return response.paths[0]

        // The server reported missing chunks (e.g. cleaned up mid-upload) —
        // resync and try again, but don't loop forever...
        if (++rounds >= 3 || ! response) throw { type: 'incomplete' }

        received = new Set(response.received || [])
    }
}

async function sendChunk(entry, index, chunkIndex, file, chunkSize, ctx) {
    let { headers, uploadState, progress, refreshPlan } = ctx

    let start = chunkIndex * chunkSize
    let blob = file.slice(start, Math.min(start + chunkSize, file.size))

    return await withRetries(async () => {
        try {
            return await sendRequest({
                method: 'post',
                url: ctx.plan.url,
                headers: { 'Accept': 'application/json', ...headers },
                body: chunkFormData(entry, chunkIndex, blob, file),
                onProgress: loaded => progress.report(loaded),
                uploadState,
            })
        } catch (error) {
            // The signed URL expired mid-upload (slow connections, long
            // files) — fetch a fresh plan and let the retry use it...
            if (error.type === 'status' && [401, 403].includes(error.status) && refreshPlan) {
                let fresh = await refreshPlan()

                if (fresh.strategy === 'chunked') {
                    ctx.plan.url = fresh.url
                    entry.id = fresh.files[index].id

                    error.refreshed = true
                }
            }

            throw error
        }
    }, {
        shouldRetry: error => error.type === 'network'
            || (error.type === 'status' && error.status >= 500)
            || error.refreshed,
    })
}

function chunkFormData(entry, chunkIndex, blob, file) {
    let formData = new FormData()

    formData.append('id', entry.id)
    formData.append('index', chunkIndex)
    formData.append('name', file.name)
    formData.append('type', file.type)
    formData.append('chunk', blob, file.name + '.part')

    return formData
}

function chunkLength(file, index, chunkSize) {
    return Math.min((index + 1) * chunkSize, file.size) - index * chunkSize
}

function allChunks(total) {
    return [...Array(total).keys()]
}
