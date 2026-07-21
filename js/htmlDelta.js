let encoder = new TextEncoder()
let decoder = new TextDecoder('utf-8', { fatal: true })
let verificationUnavailable = false

export function supportsHtmlDeltaVerification() {
    return ! verificationUnavailable
        && typeof globalThis.crypto?.subtle?.digest === 'function'
}

export async function hashHtml(html) {
    if (typeof html !== 'string' || ! supportsHtmlDeltaVerification()) {
        throw new Error('Livewire HTML delta verification is unavailable')
    }

    let digest

    try {
        digest = await globalThis.crypto.subtle.digest('SHA-256', encoder.encode(html))
    } catch (error) {
        verificationUnavailable = true

        throw error
    }

    return Array.from(
        new Uint8Array(digest),
        byte => byte.toString(16).padStart(2, '0'),
    ).join('')
}

export async function reconstructHtmlDelta(html, patches, expectedHash) {
    if (! isSha256Hash(expectedHash)) {
        throw new Error('Invalid Livewire HTML delta hash')
    }

    let reconstructed = applyHtmlDelta(html, patches)
    let actualHash = await hashHtml(reconstructed)

    if (actualHash !== expectedHash) {
        throw new Error('Livewire HTML delta integrity check failed')
    }

    return reconstructed
}

export function isSha256Hash(value) {
    return typeof value === 'string' && /^[a-f0-9]{64}$/.test(value)
}

export function applyHtmlDelta(html, patches) {
    let source = encoder.encode(html)

    // Accept the original single-patch shape during rolling deployments.
    if (! Array.isArray(patches)) patches = [patches]

    let decoded = []
    let sourceCursor = 0
    let resultLength = source.length

    patches.forEach(patch => {
        if (! patch || typeof patch !== 'object') {
            throw new Error('Invalid Livewire HTML delta patch')
        }

        let start = patch.start
        let deleteLength = patch.delete
        let insert = decodeBase64(patch.insert)

        if (! Number.isInteger(start)
            || ! Number.isInteger(deleteLength)
            || start < sourceCursor
            || deleteLength < 0
            || start + deleteLength > source.length
        ) {
            throw new Error('Invalid Livewire HTML delta range')
        }

        resultLength += insert.length - deleteLength

        if (! Number.isSafeInteger(resultLength) || resultLength < 0) {
            throw new Error('Invalid Livewire HTML delta length')
        }

        decoded.push({ start, deleteLength, insert })
        sourceCursor = start + deleteLength
    })

    let result = new Uint8Array(resultLength)
    let resultCursor = 0

    sourceCursor = 0

    decoded.forEach(({ start, deleteLength, insert }) => {
        let unchanged = source.subarray(sourceCursor, start)

        result.set(unchanged, resultCursor)
        resultCursor += unchanged.length

        result.set(insert, resultCursor)
        resultCursor += insert.length

        sourceCursor = start + deleteLength
    })

    let suffix = source.subarray(sourceCursor)

    result.set(suffix, resultCursor)

    return decoder.decode(result)
}

function decodeBase64(value) {
    if (typeof value !== 'string') {
        throw new Error('Invalid Livewire HTML delta insert')
    }

    let binary = atob(value)
    let bytes = new Uint8Array(binary.length)

    for (let index = 0; index < binary.length; index++) {
        bytes[index] = binary.charCodeAt(index)
    }

    return bytes
}
