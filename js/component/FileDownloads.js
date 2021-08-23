import store from '@/Store'

export default function () {
    store.registerHook('message.received', (message, component) => {
        let response = message.response

        if (! response.effects.download) return

        // We need to use window.webkitURL so downloads work on iOS Safari.
        let urlObject = window.webkitURL || window.URL

        let url = urlObject.createObjectURL(
            base64toBlob(response.effects.download.content, response.effects.download.contentType)
        )

        let invisibleLink = document.createElement('a')

        invisibleLink.style.display = 'none'
        invisibleLink.href = url
        invisibleLink.download = response.effects.download.name

        document.body.appendChild(invisibleLink)

        invisibleLink.click()

        setTimeout(function() {
            urlObject.revokeObjectURL(url)
        }, 0);
    })
}

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
