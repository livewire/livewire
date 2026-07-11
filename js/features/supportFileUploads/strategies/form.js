import { sendRequest } from '../request'

// Classic upload: POST every file in one multipart form request
// to Livewire's signed upload endpoint...
export default async function form({ plan, files, headers, uploadState, progress }) {
    let formData = new FormData()

    files.forEach(file => formData.append('files[]', file, file.name))

    let response = await sendRequest({
        method: 'post',
        url: plan.url,
        headers: { 'Accept': 'application/json', ...headers },
        body: formData,
        onProgress: (loaded, total) => progress.reportRatio(loaded, total),
        uploadState,
    })

    return response.paths
}
