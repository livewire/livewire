import { on } from '@/hooks'

on('commit', ({ succeed }) => {
    succeed(({ effects }) => {
        let download = effects.download

        if (! download) return

        // We need to use window.webkitURL so downloads work on iOS Safari.
        let urlObject = window.webkitURL || window.URL

        let url = urlObject.createObjectURL(
            base64toBlob(download.content, download.contentType)
        )

        let invisibleLink = document.createElement('a')

        invisibleLink.style.display = 'none'
        invisibleLink.href = url
        invisibleLink.download = download.name

        document.body.appendChild(invisibleLink)

        invisibleLink.click()

        setTimeout(function() {
            urlObject.revokeObjectURL(url)
        }, 0);
    })
})

function base64toBlob(b64Data, contentType = '', sliceSize = 512) {
    const byteCharacters = atob(b64Data)
    const byteArrays = []

    if (contentType === null) contentType = ''

    for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        let slice = byteCharacters.slice(offset, offset + sliceSize)

        let byteNumbers = new Array(slice.length)

        for (let i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i)
        }

        let byteArray = new Uint8Array(byteNumbers)

        byteArrays.push(byteArray)
    }

    return new Blob(byteArrays, { type: contentType });
}
