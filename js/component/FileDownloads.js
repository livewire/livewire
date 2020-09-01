import store from '@/Store'

export default function () {
    store.registerHook('message.received', (message, component) => {
        let response = message.response

        if (! response.effects.download) return

        let url = window.URL.createObjectURL(
            base64toBlob(response.effects.download.content)
        )

        let invisibleLink = document.createElement('a')

        invisibleLink.style.display = 'none'
        invisibleLink.href = url
        invisibleLink.download = response.effects.download.name

        document.body.appendChild(invisibleLink)

        invisibleLink.click()

        window.URL.revokeObjectURL(url)
    })
}

function base64toBlob(b64Data, contentType='', sliceSize=512) {
    const byteCharacters = atob(b64Data)
    const byteArrays = []

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
