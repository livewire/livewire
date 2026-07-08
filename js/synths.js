/**
 * JavaScript synthesizers ("synths") are the client-side counterpart to
 * Livewire's PHP property synthesizers. A PHP synth dehydrates a rich PHP
 * value (a Carbon date, a DTO, etc.) into a JSON-safe value plus a metadata
 * tuple: [value, { s: 'key', ... }]. By default, JavaScript only ever sees
 * that raw JSON value.
 *
 * Registering a JS synth for the same key upgrades the raw value into a rich
 * JavaScript object when component state is hydrated on the frontend, and
 * converts it back to its raw wire format when state is diffed and sent to
 * the server.
 */

let synths = {}

export function registerSynth(key, synth) {
    if (typeof key !== 'string' || key === '') {
        throw `Livewire.synth() requires a key matching the PHP synthesizer's static $key property`
    }

    for (let method of ['match', 'hydrate', 'dehydrate']) {
        if (typeof synth[method] !== 'function') {
            throw `Livewire.synth('${key}') requires a "${method}" function`
        }
    }

    synths[key] = synth
}

export function flushSynths() {
    synths = {}
}

export function hasSynths() {
    return Object.keys(synths).length > 0
}

/**
 * Find a registered synth whose match() recognizes the given rich value.
 * Used to identify synth values in state trees so they can be treated
 * atomically and dehydrated back to their wire format.
 */
export function findSynthByValue(value) {
    if (typeof value !== 'object' || value === null) return

    for (let key in synths) {
        if (synths[key].match(value)) return synths[key]
    }
}

/**
 * Hydrate a raw wire value into a rich JS value if a synth is registered
 * for the metadata's synth key. Otherwise pass the raw value through.
 */
export function hydrateValue(value, meta) {
    let synth = meta && synths[meta.s]

    return synth ? synth.hydrate(value, meta) : value
}

/**
 * Walk a value tree and return a copy with every rich synth value converted
 * back to its raw wire format. Never mutates the original tree.
 */
export function dehydrateTree(value) {
    if (! hasSynths()) return value

    return dehydrateTreeRecursive(value)
}

function dehydrateTreeRecursive(value) {
    let synth = findSynthByValue(value)

    if (synth) value = synth.dehydrate(value)

    if (typeof value !== 'object' || value === null) return value

    let copy = Array.isArray(value) ? [] : {}

    Object.entries(value).forEach(([key, child]) => {
        copy[key] = dehydrateTreeRecursive(child)
    })

    return copy
}
