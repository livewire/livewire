
export class Bag {
    constructor() { this.arrays = {} }

    add(key, value) {
        if (! this.arrays[key]) this.arrays[key] = []
        this.arrays[key].push(value)
    }

    remove(key) {
        if (this.arrays[key]) delete this.arrays[key]
    }

    get(key) { return this.arrays[key] || [] }

    each(key, callback) { return this.get(key).forEach(callback) }
}

export class WeakBag {
    constructor() { this.arrays = new WeakMap }

    add(key, value) {
        if (! this.arrays.has(key)) this.arrays.set(key, [])
        this.arrays.get(key).push(value)
    }

    remove(key) {
        if (this.arrays.has(key)) this.arrays.delete(key, [])
    }

    get(key) { return this.arrays.has(key) ? this.arrays.get(key) : [] }

    each(key, callback) { return this.get(key).forEach(callback) }
}

export function dispatch(target, name, detail = {}, bubbles = true) {
    target.dispatchEvent(
        new CustomEvent(name, {
            detail,
            bubbles,
            // Allows events to pass the shadow DOM barrier.
            composed: true,
            cancelable: true,
        })
    )
}

export function listen(target, name, handler) {
    target.addEventListener(name, handler)

    return () => target.removeEventListener(name, handler)
}

/**
 * Type-checking in JS is weird and annoying, these are better.
 */
export function isObjecty(subject) { return (typeof subject === 'object' && subject !== null) }
export function isObject(subject) { return (isObjecty(subject) && ! isArray(subject)) }
export function isArray(subject) { return Array.isArray(subject) }
export function isFunction(subject) { return typeof subject === 'function' }
export function isPrimitive(subject) { return typeof subject !== 'object' || subject === null }

/**
 * Clone an object deeply to wipe out any shared references.
 */
export function deepClone(obj) { return JSON.parse(JSON.stringify(obj)) }

/**
 * Determine if two objects take the exact same shape.
 */
export function deeplyEqual(a, b) { return JSON.stringify(a) === JSON.stringify(b) }

/**
 * An easy way to loop through arrays and objects.
 */
export function each(subject, callback) {
    Object.entries(subject).forEach(([key, value]) => callback(key, value))
}

/**
 * Get a property from an object with support for dot-notation.
 */
export function dataGet(object, key) {
    if (key === '') return object

    return key.split('.').reduce((carry, i) => {
        return carry?.[i]
    }, object)
}

/**
 * Set a property on an object with support for dot-notation.
 */
export function dataSet(object, key, value) {
    let segments = key.split('.')

    if (segments.length === 1) {
        return object[key] = value
    }

    let firstSegment = segments.shift()
    let restOfSegments = segments.join('.')

    if (object[firstSegment] === undefined) {
        object[firstSegment] = {}
    }

    dataSet(object[firstSegment], restOfSegments, value)
}

/**
 * Create a flat, dot-notated diff of two obejcts.
 */
export function diff(left, right, diffs = {}, path = '') {
    // Are they the same?
    if (left === right) return diffs

    // Are they COMPLETELY different?
    if (typeof left !== typeof right || (isObject(left) && isArray(right)) || (isArray(left) && isObject(right))) {
        diffs[path] = right;
        return diffs
    }

    // Is the right or left side a primitive value (a leaf node)?
    if (isPrimitive(left) || isPrimitive(right)) {
        diffs[path] = right
        return diffs
    }

    // We now know both are objects...
    let leftKeys = Object.keys(left)

    // Recursively diff the object's properties...
    Object.entries(right).forEach(([key, value]) => {
        diffs = {...diffs, ...diff(left[key], right[key], diffs, path === '' ? key : `${path}.${key}`)}
        leftKeys = leftKeys.filter(i => i !== key)
    })

    // Mark any items for removal...
    leftKeys.forEach(key => {
        diffs[`${path}.${key}`] = '__rm__'
    })

    return diffs
}

/**
 * The data that's passed between the browser and server is in the form of
 * nested tuples consisting of the schema: [rawValue, metadata]. In this
 * method we're extracting the plain JS object of only the raw values.
 */
export function extractData(payload) {
    let value = isSynthetic(payload) ? payload[0] : payload
    let meta = isSynthetic(payload) ? payload[1] : undefined

    if (isObjecty(value)) {
        Object.entries(value).forEach(([key, iValue]) => {
            value[key] = extractData(iValue)
        })
    }

    return value
}

/**
 * Determine if the variable passed in is a node in a nested metadata
 * tuple tree. (Meaning it takes the form of: [rawData, metadata])
 */
export function isSynthetic(subject) {
    return Array.isArray(subject)
        && subject.length === 2
        && typeof subject[1] === 'object'
        && Object.keys(subject[1]).includes('s')
}

/**
 * Post requests in Laravel require a csrf token to be passed
 * along with the payload. Here, we'll try and locate one.
 */
export function getCsrfToken() {
    // Purposely not caching. Fetching it fresh every time ensures we're
    // not depending on a stale session's CSRF token...

    if (document.querySelector('meta[name="csrf-token"]')) {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }

    if (document.querySelector('[data-csrf]')) {
        return document.querySelector('[data-csrf]').getAttribute('data-csrf')
    }

    if (window.livewireScriptConfig['csrf'] ?? false) {
        return window.livewireScriptConfig['csrf']
    }

    throw 'Livewire: No CSRF token detected'
}

let nonce;

export function getNonce() {
    if (nonce) return nonce


    if (window.livewireScriptConfig && (window.livewireScriptConfig['nonce'] ?? false)) {
        nonce = window.livewireScriptConfig['nonce']

        return nonce
    }

    const elWithNonce = document.querySelector('style[data-livewire-style][nonce]')

    if (elWithNonce) {
        nonce = elWithNonce.nonce

        return nonce
    }

    return null
}

/**
 * Livewire's update URI. This is configurable via Livewire::setUpdateRoute(...)
 */
export function getUpdateUri() {
    return document.querySelector('[data-update-uri]')?.getAttribute('data-update-uri') ?? window.livewireScriptConfig['uri'] ?? null
}

export function contentIsFromDump(content) {
    return !! content.match(/<script>Sfdump\(".+"\)<\/script>/)
}

export function splitDumpFromContent(content) {
    let dump = content.match(/.*<script>Sfdump\(".+"\)<\/script>/s)

    return [dump, content.replace(dump, '')]
}
