let customCodecs = new Map

let passthroughCodec = {
    toServer: value => value,
    fromServer: value => value,
}

let builtInCodecs = {
    int: {
        toServer: castInteger,
        fromServer: castInteger,
    },
    integer: {
        toServer: castInteger,
        fromServer: castInteger,
    },
    float: {
        toServer: castFloat,
        fromServer: castFloat,
    },
    double: {
        toServer: castFloat,
        fromServer: castFloat,
    },
    bool: {
        toServer: castBoolean,
        fromServer: castBoolean,
    },
    boolean: {
        toServer: castBoolean,
        fromServer: castBoolean,
    },
    string: {
        toServer: castString,
        fromServer: castString,
    },
    array: {
        toServer: castArray,
        fromServer: castArray,
    },
    object: {
        toServer: castObject,
        fromServer: castObject,
    },
}

export function registerSyncCodec(strategy, codec) {
    let normalizedStrategy = normalizeStrategy(strategy)

    if (! normalizedStrategy) {
        throw new Error('Livewire.sync() requires a non-empty strategy key.')
    }

    if (! codec || typeof codec !== 'object') {
        throw new Error('Livewire.sync() expects a codec object.')
    }

    if (typeof codec.toServer !== 'function' && typeof codec.fromServer !== 'function') {
        throw new Error('Livewire.sync() expects at least one transform function (toServer or fromServer).')
    }

    customCodecs.set(normalizedStrategy, {
        toServer: typeof codec.toServer === 'function' ? codec.toServer : passthroughCodec.toServer,
        fromServer: typeof codec.fromServer === 'function' ? codec.fromServer : passthroughCodec.fromServer,
    })

    return () => removeSyncCodec(normalizedStrategy)
}

export function removeSyncCodec(strategy) {
    let normalizedStrategy = normalizeStrategy(strategy)

    if (! normalizedStrategy) return

    customCodecs.delete(normalizedStrategy)
}

export function applySyncDataFromServer(data, syncMap = {}) {
    if (! data || typeof data !== 'object') return data

    Object.entries(syncMap || {}).forEach(([path, strategy]) => {
        if (path.includes('.')) return
        if (! Object.prototype.hasOwnProperty.call(data, path)) return

        data[path] = applyFromServer(strategy, data[path])
    })

    return data
}

export function applySyncUpdatesFromServer(updates, syncMap = {}) {
    return applySyncUpdates(updates, syncMap, applyFromServer)
}

export function applySyncUpdatesToServer(updates, syncMap = {}) {
    return applySyncUpdates(updates, syncMap, applyToServer)
}

function applySyncUpdates(updates, syncMap, transform) {
    return Object.fromEntries(
        Object.entries(updates || {}).map(([path, value]) => {
            if (path.includes('.')) return [path, value]
            if (! Object.prototype.hasOwnProperty.call(syncMap || {}, path)) return [path, value]

            return [path, transform(syncMap[path], value)]
        })
    )
}

function applyToServer(strategy, value) {
    return resolveCodec(strategy).toServer(value)
}

function applyFromServer(strategy, value) {
    return resolveCodec(strategy).fromServer(value)
}

function resolveCodec(strategy) {
    let normalizedStrategy = normalizeStrategy(strategy)

    if (! normalizedStrategy) return passthroughCodec

    return customCodecs.get(normalizedStrategy)
        || builtInCodecs[normalizedStrategy.toLowerCase()]
        || passthroughCodec
}

function normalizeStrategy(strategy) {
    if (typeof strategy !== 'string') return null

    let normalizedStrategy = strategy.trim()

    if (normalizedStrategy === '') return null

    return normalizedStrategy
}

function castInteger(value) {
    if (value === null || value === '') return null
    if (typeof value === 'number' && Number.isInteger(value) && Number.isFinite(value)) return value
    if (typeof value === 'boolean') return value ? 1 : 0

    if (typeof value === 'string' && /^-?\d+$/.test(value.trim())) {
        return Number.parseInt(value, 10)
    }

    return value
}

function castFloat(value) {
    if (value === null || value === '') return null
    if (typeof value === 'number' && Number.isFinite(value)) return value

    if (typeof value === 'string' && value.trim() !== '' && ! Number.isNaN(Number(value))) {
        return Number(value)
    }

    return value
}

function castBoolean(value) {
    if (typeof value === 'boolean') return value
    if (value === null || value === '') return false
    if (typeof value === 'number') return value !== 0

    if (typeof value === 'string') {
        let normalized = value.trim().toLowerCase()

        if (['1', 'true', 'on', 'yes'].includes(normalized)) return true
        if (['0', 'false', 'off', 'no', ''].includes(normalized)) return false
    }

    return value
}

function castString(value) {
    if (value === null) return null
    if (typeof value === 'string') return value
    if (typeof value === 'number' || typeof value === 'boolean' || typeof value === 'bigint') return `${value}`

    return value
}

function castArray(value) {
    if (value === null || value === '') return []
    if (Array.isArray(value)) return value

    if (typeof value === 'string') {
        try {
            let parsed = JSON.parse(value)

            if (Array.isArray(parsed)) return parsed
        } catch (e) {
            //
        }
    }

    return value
}

function castObject(value) {
    if (value === null || value === '') return {}
    if (isPlainObject(value)) return value
    if (Array.isArray(value)) return Object.assign({}, value)

    if (typeof value === 'string') {
        try {
            let parsed = JSON.parse(value)

            if (isPlainObject(parsed)) return parsed
        } catch (e) {
            //
        }
    }

    return value
}

function isPlainObject(value) {
    return typeof value === 'object' && value !== null && ! Array.isArray(value)
}
