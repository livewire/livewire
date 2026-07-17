import { applyHtmlDelta, hashHtml, isSha256Hash } from './htmlDelta'

let encoder = new TextEncoder()
let decoder = new TextDecoder('utf-8', { fatal: true })
let maximumOutputBytes = 128 * 1024 * 1024
let maximumOperations = 65536
let minimumBlockSize = 256
let maximumBlockSize = 65536
let crc32Table = buildCrc32Table()

export function buildBlockManifest(html, blockSize = 2048) {
    let source = encodeUtf8(html, 'render baseline')

    validateBlockSize(blockSize)

    let blockCount = Math.ceil(source.length / blockSize)

    if (blockCount > maximumOperations) {
        throw new Error('Livewire render block manifest is too large')
    }

    let packed = new Uint8Array(blockCount * 8)
    let view = new DataView(packed.buffer)

    for (let index = 0; index < blockCount; index++) {
        let start = index * blockSize
        let block = source.subarray(start, Math.min(start + blockSize, source.length))

        view.setUint32(index * 8, rsyncWeakChecksum(block), false)
        view.setUint32(index * 8 + 4, crc32(block), false)
    }

    return Object.freeze({
        blockSize,
        blocks: encodeBase64(packed),
    })
}

export function applyChunkOps(baselineHtml, ops, expectedBytes = null) {
    let source = encodeUtf8(baselineHtml, 'render baseline')

    validateOperationList(ops, 'chunk')
    validateOptionalByteLength(expectedBytes)

    let decoded = []
    let resultLength = 0

    ops.forEach(op => {
        if (! Array.isArray(op)) {
            throw new Error('Invalid Livewire render chunk operation')
        }

        if (op[0] === 'c') {
            if (op.length !== 3
                || ! Number.isSafeInteger(op[1])
                || ! Number.isSafeInteger(op[2])
                || op[1] < 0
                || op[2] <= 0
                || ! Number.isSafeInteger(op[1] + op[2])
                || op[1] + op[2] > source.length
            ) {
                throw new Error('Invalid Livewire render chunk copy range')
            }

            decoded.push({
                bytes: source.subarray(op[1], op[1] + op[2]),
            })

            resultLength = addOutputLength(resultLength, op[2])

            return
        }

        if (op[0] === 'a') {
            if (op.length !== 2) {
                throw new Error('Invalid Livewire render chunk add operation')
            }

            let bytes = decodeBase64(op[1], 'chunk add')

            if (bytes.length === 0) {
                throw new Error('Invalid Livewire render chunk add operation')
            }

            decoded.push({ bytes })
            resultLength = addOutputLength(resultLength, bytes.length)

            return
        }

        throw new Error('Invalid Livewire render chunk operation')
    })

    if (expectedBytes !== null && resultLength !== expectedBytes) {
        throw new Error('Livewire render chunk length does not match its descriptor')
    }

    let result = new Uint8Array(resultLength)
    let cursor = 0

    decoded.forEach(part => {
        result.set(part.bytes, cursor)
        cursor += part.bytes.length
    })

    return decodeUtf8(result, 'chunk result')
}

export function parseTransportFragments(html) {
    let source = encodeUtf8(html, 'fragment source')
    let markerPattern = /<!--\[if (FRAGMENT|ENDFRAGMENT):([^\]]*)\]><!\[endif\]-->/g
    let markerPrefixPattern = /<!--\[if (?:END)?FRAGMENT:/g
    let markerPrefixCount = Array.from(html.matchAll(markerPrefixPattern)).length
    let markerCount = 0
    let stack = []
    let drafts = []
    let identities = new Set()
    let match

    while ((match = markerPattern.exec(html)) !== null) {
        markerCount++

        let markerType = match[1]
        let encodedMetadata = match[2]
        let metadata = parseFragmentMetadata(encodedMetadata)

        if (markerType === 'FRAGMENT') {
            let transport = metadata.type === 'transport'
            let parentIndex = null

            if (transport) {
                for (let index = stack.length - 1; index >= 0; index--) {
                    if (stack[index].transportIndex !== null) {
                        parentIndex = stack[index].transportIndex

                        break
                    }
                }
            }

            let transportIndex = null

            if (transport) {
                let token = transportFragmentToken(metadata)

                if (identities.has(token)) {
                    throw new Error('Duplicate Livewire transport fragment token')
                }

                identities.add(token)
                transportIndex = drafts.length

                drafts.push({
                    index: transportIndex,
                    token,
                    metadata,
                    parentIndex,
                    depth: parentIndex === null ? 0 : drafts[parentIndex].depth + 1,
                    startChar: match.index,
                    contentStartChar: markerPattern.lastIndex,
                    contentEndChar: null,
                    endChar: null,
                })
            }

            stack.push({
                encodedMetadata,
                transportIndex,
            })

            continue
        }

        if (stack.length === 0) {
            throw new Error('Unmatched Livewire fragment end marker')
        }

        let opened = stack.pop()

        if (opened.encodedMetadata !== encodedMetadata) {
            throw new Error('Mismatched Livewire fragment markers')
        }

        if (opened.transportIndex !== null) {
            drafts[opened.transportIndex].contentEndChar = match.index
            drafts[opened.transportIndex].endChar = markerPattern.lastIndex
        }
    }

    if (markerCount !== markerPrefixCount) {
        throw new Error('Malformed Livewire fragment marker')
    }

    if (stack.length !== 0) {
        throw new Error('Unclosed Livewire fragment marker')
    }

    let positions = [0, html.length]

    drafts.forEach(fragment => {
        positions.push(
            fragment.startChar,
            fragment.contentStartChar,
            fragment.contentEndChar,
            fragment.endChar,
        )
    })

    let offsets = mapUtf8Offsets(html, positions)
    let fragments = drafts.map(fragment => Object.freeze({
        index: fragment.index,
        token: fragment.token,
        metadata: fragment.metadata,
        parentIndex: fragment.parentIndex,
        depth: fragment.depth,
        start: offsets.get(fragment.startChar),
        contentStart: offsets.get(fragment.contentStartChar),
        contentEnd: offsets.get(fragment.contentEndChar),
        end: offsets.get(fragment.endChar),
        startChar: fragment.startChar,
        contentStartChar: fragment.contentStartChar,
        contentEndChar: fragment.contentEndChar,
        endChar: fragment.endChar,
    }))

    if (offsets.get(html.length) !== source.length) {
        throw new Error('Invalid Livewire fragment byte offsets')
    }

    return Object.freeze(fragments)
}

export function buildFragmentManifest(html) {
    let source = encodeUtf8(html, 'fragment source')
    let fragments = parseTransportFragments(html)
    let children = new Map()

    fragments.forEach(fragment => {
        let parent = fragment.parentIndex === null ? -1 : fragment.parentIndex

        if (! children.has(parent)) children.set(parent, [])

        children.get(parent).push(fragment.index)
    })

    let rootSkeleton = buildSkeleton(
        source,
        0,
        source.length,
        fragments,
        children.get(-1) || [],
    )
    let nodes = fragments.map(fragment => {
        let content = source.subarray(fragment.contentStart, fragment.contentEnd)
        let skeleton = buildSkeleton(
            source,
            fragment.contentStart,
            fragment.contentEnd,
            fragments,
            children.get(fragment.index) || [],
        )

        return Object.freeze([
            fragment.token,
            contentDigest(content),
            contentDigest(skeleton),
        ])
    })

    return Object.freeze({
        root: contentDigest(rootSkeleton),
        nodes: Object.freeze(nodes),
    })
}

export function applyFragmentOps(baselineHtml, ops, expectedBytes = null) {
    let source = encodeUtf8(baselineHtml, 'render baseline')
    let fragments = parseTransportFragments(baselineHtml)
    let byToken = new Map(fragments.map(fragment => [fragment.token, fragment]))

    validateOperationList(ops, 'fragment')
    validateOptionalByteLength(expectedBytes)

    let selected = new Set()
    let replacements = []

    ops.forEach(op => {
        if (! Array.isArray(op)
            || op.length !== 2
            || typeof op[0] !== 'string'
            || op[0].length === 0
            || op[0].length > 256
            || typeof op[1] !== 'string'
        ) {
            throw new Error('Invalid Livewire render fragment operation')
        }

        if (selected.has(op[0])) {
            throw new Error('Duplicate Livewire render fragment operation')
        }

        let fragment = byToken.get(op[0])

        if (! fragment) {
            throw new Error('Unknown Livewire render fragment token')
        }

        let insert = encodeUtf8(op[1], 'fragment replacement')

        selected.add(op[0])
        replacements.push({
            fragment,
            start: fragment.contentStart,
            end: fragment.contentEnd,
            insert,
        })
    })

    replacements.forEach(replacement => {
        let parentIndex = replacement.fragment.parentIndex

        while (parentIndex !== null) {
            let parent = fragments[parentIndex]

            if (selected.has(parent.token)) {
                throw new Error('Overlapping Livewire render fragment operations')
            }

            parentIndex = parent.parentIndex
        }
    })

    replacements.sort((left, right) => left.start - right.start)

    for (let index = 1; index < replacements.length; index++) {
        if (replacements[index].start < replacements[index - 1].end) {
            throw new Error('Overlapping Livewire render fragment operations')
        }
    }

    let result = applyByteReplacements(source, replacements)

    if (expectedBytes !== null && result.length !== expectedBytes) {
        throw new Error('Livewire render fragment length does not match its descriptor')
    }

    return decodeUtf8(result, 'fragment result')
}

export async function materializeRender(render, fullHtml = null, baseline = null) {
    validateRenderDescriptor(render)

    let mode = render.mode
    let result

    if (mode === 'full') {
        if (typeof fullHtml !== 'string') {
            throw new Error('Missing full Livewire render HTML')
        }

        if (hasOwn(render, 'base')
            || hasOwn(render, 'patches')
            || hasOwn(render, 'ops')
        ) {
            throw new Error('Invalid full Livewire render descriptor')
        }

        result = fullHtml
    } else {
        let captured = captureBaseline(baseline)

        if (! isSha256Hash(render.base) || render.base !== captured.hash) {
            throw new Error('Livewire render baseline does not match its descriptor')
        }

        let actualBaseHash = await hashHtml(captured.html)

        if (actualBaseHash !== captured.hash) {
            throw new Error('Livewire render baseline failed its integrity check')
        }

        if (mode === 'same') {
            if (hasOwn(render, 'patches')
                || hasOwn(render, 'ops')
                || render.target !== render.base
                || render.bytes !== captured.bytes
            ) {
                throw new Error('Invalid same Livewire render descriptor')
            }

            result = captured.html

            // The baseline hash and byte length were verified above and same
            // requires target === base, so a second full SHA-256 pass is redundant.
            return result
        } else if (mode === 'splice') {
            if (! Array.isArray(render.patches) || hasOwn(render, 'ops')) {
                throw new Error('Invalid splice Livewire render descriptor')
            }

            validateBytePatches(captured.html, render.patches, render.bytes)
            result = applyHtmlDelta(captured.html, render.patches)
        } else if (mode === 'chunks') {
            if (! Array.isArray(render.ops) || hasOwn(render, 'patches')) {
                throw new Error('Invalid chunks Livewire render descriptor')
            }

            result = applyChunkOps(captured.html, render.ops, render.bytes)
        } else if (mode === 'fragments') {
            if (! Array.isArray(render.ops) || hasOwn(render, 'patches')) {
                throw new Error('Invalid fragments Livewire render descriptor')
            }

            result = applyFragmentOps(captured.html, render.ops, render.bytes)
        }
    }

    await verifyMaterializedValue(result, render.target, render.bytes, 'render')

    return result
}

export async function materializeSnapshotDelta(delta, baselineSnapshot) {
    if (! isRecord(delta)
        || delta.v !== 1
        || ! isSha256Hash(delta.base)
        || ! isSha256Hash(delta.target)
        || ! isValidByteLength(delta.bytes)
        || ! Array.isArray(delta.patches)
        || typeof baselineSnapshot !== 'string'
    ) {
        throw new Error('Invalid Livewire snapshot delta')
    }

    let capturedSnapshot = baselineSnapshot
    let actualBaseHash = await hashHtml(capturedSnapshot)

    if (actualBaseHash !== delta.base) {
        throw new Error('Livewire snapshot delta baseline failed its integrity check')
    }

    validateBytePatches(capturedSnapshot, delta.patches, delta.bytes)

    if (delta.patches.length === 0 && delta.target === delta.base) {
        return capturedSnapshot
    }

    let result = applyHtmlDelta(capturedSnapshot, delta.patches)

    await verifyMaterializedValue(result, delta.target, delta.bytes, 'snapshot delta')

    return result
}

export async function createGzipBody(value) {
    if (typeof value !== 'string') {
        throw new Error('Invalid Livewire request body')
    }

    let source = encodeUtf8(value, 'request body')

    if (typeof globalThis.CompressionStream !== 'function'
        || typeof globalThis.Response !== 'function'
    ) {
        return null
    }

    try {
        let compression = new globalThis.CompressionStream('gzip')
        let output = new globalThis.Response(compression.readable)
            .arrayBuffer()
            .catch(() => null)
        let writer = compression.writable.getWriter()

        await writer.write(source)
        await writer.close()

        let buffer = await output

        if (buffer === null) return null

        return new Uint8Array(buffer)
    } catch (error) {
        return null
    }
}

export {
    materializeRender as materializeRenderDescriptor,
    materializeSnapshotDelta as reconstructSnapshotDelta,
}

function validateRenderDescriptor(render) {
    let modes = ['full', 'same', 'splice', 'chunks', 'fragments']

    if (! isRecord(render)
        || render.v !== 1
        || ! modes.includes(render.mode)
        || ! isSha256Hash(render.target)
        || ! isValidByteLength(render.bytes)
        || hasOwn(render, 'html')
    ) {
        throw new Error('Invalid Livewire render descriptor')
    }

    if (render.mode !== 'full' && ! isSha256Hash(render.base)) {
        throw new Error('Invalid Livewire render baseline hash')
    }

    if (hasOwn(render, 'requestGzip')
        && (! Number.isSafeInteger(render.requestGzip)
            || render.requestGzip < 1
            || render.requestGzip > 16 * 1024 * 1024)
    ) {
        throw new Error('Invalid Livewire request compression threshold')
    }

    if (hasOwn(render, 'stats')) {
        let stats = render.stats

        if (! isRecord(stats)
            || ! isValidByteLength(stats.full)
            || ! isValidByteLength(stats.selected)
        ) {
            throw new Error('Invalid Livewire render statistics')
        }
    }
}

function captureBaseline(baseline) {
    if (! isRecord(baseline)) {
        throw new Error('Missing Livewire render baseline')
    }

    let html = baseline.html
    let hash = baseline.hash
    let bytes = baseline.bytes
    let revision = baseline.revision

    if (typeof html !== 'string'
        || ! isSha256Hash(hash)
        || ! isValidByteLength(bytes)
        || (revision !== undefined
            && (! Number.isSafeInteger(revision) || revision < 0))
    ) {
        throw new Error('Invalid Livewire render baseline')
    }

    if (encodeUtf8(html, 'render baseline').length !== bytes) {
        throw new Error('Livewire render baseline byte length is invalid')
    }

    return Object.freeze({ html, hash, bytes })
}

function validateBytePatches(sourceValue, patches, expectedBytes) {
    let source = encodeUtf8(sourceValue, 'delta baseline')

    validateOperationList(patches, 'patch')
    validateOptionalByteLength(expectedBytes)

    let cursor = 0
    let resultLength = source.length

    patches.forEach(patch => {
        if (! isRecord(patch)
            || ! Number.isSafeInteger(patch.start)
            || ! Number.isSafeInteger(patch.delete)
            || patch.start < cursor
            || patch.delete < 0
            || ! Number.isSafeInteger(patch.start + patch.delete)
            || patch.start + patch.delete > source.length
        ) {
            throw new Error('Invalid Livewire byte patch range')
        }

        let insert = decodeBase64(patch.insert, 'byte patch insert')

        resultLength = addOutputLength(
            resultLength - patch.delete,
            insert.length,
        )
        cursor = patch.start + patch.delete
    })

    if (expectedBytes !== null && resultLength !== expectedBytes) {
        throw new Error('Livewire byte patch length does not match its descriptor')
    }
}

async function verifyMaterializedValue(value, expectedHash, expectedBytes, label) {
    let bytes = encodeUtf8(value, label)

    if (bytes.length !== expectedBytes) {
        throw new Error('Livewire ' + label + ' byte length verification failed')
    }

    let actualHash = await hashHtml(value)

    if (actualHash !== expectedHash) {
        throw new Error('Livewire ' + label + ' integrity verification failed')
    }
}

function applyByteReplacements(source, replacements) {
    let resultLength = source.length

    replacements.forEach(replacement => {
        resultLength = addOutputLength(
            resultLength - (replacement.end - replacement.start),
            replacement.insert.length,
        )
    })

    let result = new Uint8Array(resultLength)
    let sourceCursor = 0
    let resultCursor = 0

    replacements.forEach(replacement => {
        let unchanged = source.subarray(sourceCursor, replacement.start)

        result.set(unchanged, resultCursor)
        resultCursor += unchanged.length

        result.set(replacement.insert, resultCursor)
        resultCursor += replacement.insert.length
        sourceCursor = replacement.end
    })

    result.set(source.subarray(sourceCursor), resultCursor)

    return result
}

function buildSkeleton(source, start, end, fragments, childIndices) {
    let children = childIndices
        .map(index => fragments[index])
        .sort((left, right) => left.contentStart - right.contentStart)
    let length = end - start

    children.forEach(child => {
        if (child.contentStart < start
            || child.contentEnd > end
            || child.contentStart > child.contentEnd
        ) {
            throw new Error('Invalid Livewire transport fragment hierarchy')
        }

        length -= child.contentEnd - child.contentStart
    })

    let skeleton = new Uint8Array(length)
    let sourceCursor = start
    let resultCursor = 0

    children.forEach(child => {
        let unchanged = source.subarray(sourceCursor, child.contentStart)

        skeleton.set(unchanged, resultCursor)
        resultCursor += unchanged.length
        sourceCursor = child.contentEnd
    })

    skeleton.set(source.subarray(sourceCursor, end), resultCursor)

    return skeleton
}

function mapUtf8Offsets(value, positions) {
    let sorted = Array.from(new Set(positions)).sort((left, right) => left - right)
    let offsets = new Map()
    let characterCursor = 0
    let byteCursor = 0

    sorted.forEach(position => {
        if (! Number.isSafeInteger(position)
            || position < characterCursor
            || position > value.length
        ) {
            throw new Error('Invalid Livewire fragment character offset')
        }

        byteCursor += encoder.encode(value.slice(characterCursor, position)).length
        characterCursor = position
        offsets.set(position, byteCursor)
    })

    return offsets
}

function parseFragmentMetadata(encoded) {
    if (typeof encoded !== 'string' || encoded.length === 0 || encoded.length > 4096) {
        throw new Error('Invalid Livewire fragment metadata')
    }

    let metadata = Object.create(null)

    encoded.split('|').forEach(pair => {
        let separator = pair.indexOf('=')

        if (separator <= 0
            || separator === pair.length - 1
            || hasOwn(metadata, pair.slice(0, separator))
        ) {
            throw new Error('Invalid Livewire fragment metadata')
        }

        let key = pair.slice(0, separator)
        let value = pair.slice(separator + 1)

        if (! /^[A-Za-z][A-Za-z0-9_-]*$/.test(key)
            || value.includes('|')
            || value.includes(']')
        ) {
            throw new Error('Invalid Livewire fragment metadata')
        }

        metadata[key] = value
    })

    return Object.freeze(metadata)
}

function transportFragmentToken(metadata) {
    let candidates = ['token', 'id', 'name', 'key']

    for (let index = 0; index < candidates.length; index++) {
        let value = metadata[candidates[index]]

        if (typeof value === 'string' && value.length > 0 && value.length <= 256) {
            return value
        }
    }

    throw new Error('Missing Livewire transport fragment token')
}

function contentDigest(bytes) {
    return uint32Hex(crc32(bytes)) + uint32Hex(adler32(bytes))
}

function rsyncWeakChecksum(bytes) {
    let a = 0
    let b = 0

    for (let index = 0; index < bytes.length; index++) {
        a = (a + bytes[index]) & 0xffff
        b = (b + (bytes.length - index) * bytes[index]) & 0xffff
    }

    return ((b << 16) | a) >>> 0
}

function crc32(bytes) {
    let value = 0xffffffff

    for (let index = 0; index < bytes.length; index++) {
        value = crc32Table[(value ^ bytes[index]) & 0xff] ^ (value >>> 8)
    }

    return (value ^ 0xffffffff) >>> 0
}

function adler32(bytes) {
    let a = 1
    let b = 0

    for (let index = 0; index < bytes.length; index++) {
        a = (a + bytes[index]) % 65521
        b = (b + a) % 65521
    }

    return ((b << 16) | a) >>> 0
}

function buildCrc32Table() {
    let table = new Uint32Array(256)

    for (let index = 0; index < table.length; index++) {
        let value = index

        for (let bit = 0; bit < 8; bit++) {
            value = (value & 1) !== 0
                ? 0xedb88320 ^ (value >>> 1)
                : value >>> 1
        }

        table[index] = value >>> 0
    }

    return table
}

function uint32Hex(value) {
    return (value >>> 0).toString(16).padStart(8, '0')
}

function encodeUtf8(value, label) {
    if (typeof value !== 'string') {
        throw new Error('Invalid Livewire ' + label)
    }

    let bytes = encoder.encode(value)

    if (bytes.length > maximumOutputBytes) {
        throw new Error('Livewire ' + label + ' is too large')
    }

    if (decoder.decode(bytes) !== value) {
        throw new Error('Invalid UTF-8 in Livewire ' + label)
    }

    return bytes
}

function decodeUtf8(bytes, label) {
    try {
        return decoder.decode(bytes)
    } catch (error) {
        throw new Error('Invalid UTF-8 in Livewire ' + label)
    }
}

function encodeBase64(bytes) {
    let chunks = []
    let chunkSize = 8192

    for (let start = 0; start < bytes.length; start += chunkSize) {
        let end = Math.min(start + chunkSize, bytes.length)
        let binary = ''

        for (let index = start; index < end; index++) {
            binary += String.fromCharCode(bytes[index])
        }

        chunks.push(binary)
    }

    return btoa(chunks.join(''))
}

function decodeBase64(value, label) {
    if (typeof value !== 'string'
        || value.length > Math.ceil(maximumOutputBytes / 3) * 4
        || ! /^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/.test(value)
    ) {
        throw new Error('Invalid base64 in Livewire ' + label)
    }

    let binary

    try {
        binary = atob(value)
    } catch (error) {
        throw new Error('Invalid base64 in Livewire ' + label)
    }

    let bytes = new Uint8Array(binary.length)

    for (let index = 0; index < binary.length; index++) {
        bytes[index] = binary.charCodeAt(index)
    }

    if (encodeBase64(bytes) !== value) {
        throw new Error('Non-canonical base64 in Livewire ' + label)
    }

    return bytes
}

function validateBlockSize(blockSize) {
    if (! Number.isSafeInteger(blockSize)
        || blockSize < minimumBlockSize
        || blockSize > maximumBlockSize
    ) {
        throw new Error('Invalid Livewire render block size')
    }
}

function validateOperationList(ops, label) {
    if (! Array.isArray(ops) || ops.length > maximumOperations) {
        throw new Error('Invalid Livewire render ' + label + ' operations')
    }
}

function validateOptionalByteLength(value) {
    if (value !== null && ! isValidByteLength(value)) {
        throw new Error('Invalid Livewire render byte length')
    }
}

function isValidByteLength(value) {
    return Number.isSafeInteger(value)
        && value >= 0
        && value <= maximumOutputBytes
}

function addOutputLength(current, addition) {
    let result = current + addition

    if (! Number.isSafeInteger(result)
        || result < 0
        || result > maximumOutputBytes
    ) {
        throw new Error('Invalid Livewire render output length')
    }

    return result
}

function isRecord(value) {
    if (value === null || typeof value !== 'object' || Array.isArray(value)) {
        return false
    }

    let prototype = Object.getPrototypeOf(value)

    return prototype === Object.prototype || prototype === null
}

function hasOwn(value, key) {
    return Object.prototype.hasOwnProperty.call(value, key)
}
