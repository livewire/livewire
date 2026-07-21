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
 *
 * A synth may also define an optional merge(existing, incoming) function.
 * When a server round-trip changes a rich value, the patcher normally
 * replaces the existing instance with the freshly hydrated one. A synth
 * that defines merge() instead updates the existing instance in place, so
 * its identity survives server-driven changes for the lifetime of the
 * component. (Explicit userland replacement — `$wire.foo = new Thing` —
 * still replaces; that's the user's call.)
 *
 * Element binding is a contract on the VALUE, not the synth: a rich value
 * anywhere in state may define interceptWireModel(el, helpers) to decide how
 * elements wire:model to it — see wire-model.js for the contract.
 */

let synths = {}
let synthList = []

// Which synth produced (or matched) each rich value — an O(1) shortcut so
// hot paths (diffing, dehydration) don't re-run every synth's match()...
let synthByValue = new WeakMap()

export function registerSynth(key, synth) {
    if (typeof key !== 'string' || key === '') {
        throw `Livewire.synth() requires a key matching the PHP synthesizer's static $key property`
    }

    for (let method of ['match', 'hydrate', 'dehydrate']) {
        if (typeof synth[method] !== 'function') {
            throw `Livewire.synth('${key}') requires a "${method}" function`
        }
    }

    if (synth.merge !== undefined && typeof synth.merge !== 'function') {
        throw `Livewire.synth('${key}') expects "merge" to be a function`
    }

    if (synths[key]) synthList = synthList.filter(i => i !== synths[key])

    synths[key] = synth
    synthList.push(synth)
}

export function flushSynths() {
    synths = {}
    synthList = []
    synthByValue = new WeakMap()
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

    let memo = synthByValue.get(value)

    if (memo) return memo

    for (let i = 0; i < synthList.length; i++) {
        if (synthList[i].match(value)) {
            synthByValue.set(value, synthList[i])

            return synthList[i]
        }
    }
}

/**
 * Hydrate a raw wire value into a rich JS value if a synth is registered
 * for the metadata's synth key. Otherwise pass the raw value through.
 * Context ({ component, path }) tells the rich value where it lives.
 */
export function hydrateValue(value, meta, context = undefined) {
    let synth = meta && synths[meta.s]

    if (! synth) return value

    let rich = synth.hydrate(value, meta, context)

    if (typeof rich === 'object' && rich !== null) synthByValue.set(rich, synth)

    return rich
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

    if (copy === null) return value

    // Values that dehydrate to undefined have no wire representation yet
    // (e.g. pending uploads) — omit them entirely so they can't corrupt
    // the payload as JSON nulls...
    return Array.isArray(copy) ? copy.filter(child => child !== undefined) : copy
}
