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
let synthList = []

export function registerSynth(key, synth) {
    if (typeof key !== 'string' || key === '') {
        throw `Livewire.synth() requires a key matching the PHP synthesizer's static $key property`
    }

    for (let method of ['match', 'hydrate', 'dehydrate']) {
        if (typeof synth[method] !== 'function') {
            throw `Livewire.synth('${key}') requires a "${method}" function`
        }
    }

    if (synths[key]) synthList = synthList.filter(i => i !== synths[key])

    synths[key] = synth
    synthList.push(synth)
}

export function flushSynths() {
    synths = {}
    synthList = []
}

export function hasSynths() {
    return synthList.length > 0
}

/**
 * Find a registered synth whose match() recognizes the given rich value.
 * Used to identify synth values in state trees so they can be treated
 * atomically and dehydrated back to their wire format.
 */
export function findSynthByValue(value) {
    if (typeof value !== 'object' || value === null) return

    for (let i = 0; i < synthList.length; i++) {
        if (synthList[i].match(value)) return synthList[i]
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
 * Walk a value tree and convert every rich synth value back to its raw wire
 * format. Never mutates the original tree. Copy-on-write: subtrees without
 * rich values are returned by reference, so trees of plain data pass through
 * with zero allocations.
 */
export function dehydrateTree(value) {
    if (! hasSynths()) return value

    return dehydrateTreeRecursive(value)
}

function dehydrateTreeRecursive(value) {
    let synth = findSynthByValue(value)

    if (synth) value = synth.dehydrate(value)

    if (typeof value !== 'object' || value === null) return value

    let copy = null

    for (let key in value) {
        let child = value[key]

        // Primitives can never be rich values, so skip recursing into them...
        if (typeof child !== 'object' || child === null) continue

        let dehydrated = dehydrateTreeRecursive(child)

        // Only clone this node once a descendant actually changed...
        if (copy === null && dehydrated !== child) {
            copy = Array.isArray(value) ? [...value] : { ...value }
        }

        if (copy !== null) copy[key] = dehydrated
    }

    return copy ?? value
}
