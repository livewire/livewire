
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
 * Parse a path string into segments, supporting dot and bracket notation.
 * e.g. "foo.bar", "foo[0].bar", "foo['hey'].baz"
 */
function parsePathSegments(path) {
    if (path === '') return []

    return path
        .replace(/\[(['"]?)(.+?)\1\]/g, '.$2')  // Convert brackets to dots: foo['bar'] → foo.bar, foo[0] → foo.0
        .replace(/^\./, '')                      // Remove leading dot if present
        .split('.')
}

/**
 * Get a property from an object with support for dot-notation and bracket-notation.
 */
export function dataGet(object, key) {
    if (key === '') return object

    return parsePathSegments(key).reduce((carry, i) => {
        return carry?.[i]
    }, object)
}

/**
 * Set a property on an object with support for dot-notation and bracket-notation.
 */
export function dataSet(object, key, value) {
    let segments = parsePathSegments(key)

    if (segments.length === 1) {
        return object[segments[0]] = value
    }

    let firstSegment = segments.shift()
    let restOfSegments = segments.join('.')
    let nextSegment = segments[0]

    if (object[firstSegment] === undefined) {
        object[firstSegment] = {}
    }

    // If we're about to set a numeric key that would create null gaps, convert to object.
    // This prevents JavaScript from filling intermediate indices with nulls
    // (e.g., arr[1000] = true creates 1000 null entries in a JS array).
    // We only convert when the key would create gaps (key > length), not when appending.
    if (isArray(object[firstSegment]) && isNumeric(nextSegment) && parseInt(nextSegment) > object[firstSegment].length) {
        object[firstSegment] = { ...object[firstSegment] }
    }

    dataSet(object[firstSegment], restOfSegments, value)
}

function isNumeric(subject) {
    return ! isNaN(parseInt(subject))
}

/**
 * Delete a property from an object with support for dot-notation and bracket-notation.
 */
export function dataDelete(object, key) {
    let segments = parsePathSegments(key)

    if (segments.length === 1) {
        if (Array.isArray(object)) {
            object.splice(segments[0], 1)
        } else {
            delete object[segments[0]]
        }
        return
    }

    let firstSegment = segments.shift()
    let restOfSegments = segments.join('.')

    if (object[firstSegment] !== undefined) {
        dataDelete(object[firstSegment], restOfSegments)
    }
}

/**
 * Create a flat, dot-notated diff of two objects.
 * @deprecated Use diffAndConsolidate instead for smarter update consolidation
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
 * Create a flat, dot-notated diff of two objects with automatic consolidation.
 * When multiple items in an array/object change or the size changes,
 * the diff is consolidated to the parent level instead of individual items.
 */
export function diffAndConsolidate(left, right) {
    let diffs = {}

    diffRecursive(left, right, '', diffs, left, right)

    return diffs
}

function diffRecursive(left, right, path, diffs, rootLeft, rootRight) {
    // Are they the same?
    if (left === right) return { changed: false, consolidated: false }

    // Track if we're doing a type conversion from empty array/null/undefined to object
    // In this case, we want granular diffs, not consolidation
    let convertedToObject = false

    // Helper to check if an array has non-numeric (string) keys
    // Arrays with string keys need granular diffs because JSON.stringify ignores them
    let hasNonNumericKeys = (arr) => {
        return isArray(arr) && Object.keys(arr).some(k => isNaN(parseInt(k)))
    }

    // Are they COMPLETELY different types?
    if (typeof left !== typeof right || (isObject(left) && isArray(right)) || (isArray(left) && isObject(right))) {
        // Special case: if left is an empty array and right is an object,
        // treat the empty array as an empty object so we get granular diffs.
        // This handles the case where wire:model.live="tableFilters.filter_1.value"
        // updates tableFilters from [] to { filter_1: { value: 'foo' } }
        if (isArray(left) && left.length === 0 && isObject(right)) {
            left = {}
            convertedToObject = true
            // Fall through to continue with object comparison below
        }
        // Special case: if left is undefined/null and right is an object,
        // treat left as an empty object to get granular diffs for new properties
        else if ((left === undefined || left === null) && isObject(right)) {
            left = {}
            convertedToObject = true
            // Fall through to continue with object comparison below
        } else {
            diffs[path] = right
            return { changed: true, consolidated: false }
        }
    }

    // Special case: if both are arrays but right has non-numeric keys,
    // we need granular diffs because JSON.stringify ignores string keys on arrays.
    // Treat both as objects to properly diff the string keys.
    if (isArray(left) && isArray(right) && hasNonNumericKeys(right)) {
        // Treat the arrays as objects for comparison
        // If left was empty, mark as converted to object to prevent consolidation
        if (Object.keys(left).length === 0) {
            convertedToObject = true
        }
    }

    // Is either side a primitive value (a leaf node)?
    if (isPrimitive(left) || isPrimitive(right)) {
        diffs[path] = right
        return { changed: true, consolidated: false }
    }

    // Both are objects/arrays - check if we should consolidate at this level
    let leftKeys = Object.keys(left)
    let rightKeys = Object.keys(right)

    // If the size changed, consolidate at this level
    // BUT if we converted to object, don't consolidate - we're adding new items
    // and should produce granular diffs for query string handling
    if (leftKeys.length !== rightKeys.length && !convertedToObject) {
        // For root level, we can't consolidate to a single key, so diff each root property
        if (path === '') {
            Object.keys(right).forEach(key => {
                if (!deeplyEqual(left[key], right[key])) {
                    diffs[key] = right[key]
                }
            })
            return { changed: true, consolidated: true }
        }
        diffs[path] = dataGet(rootRight, path)
        return { changed: true, consolidated: true }
    }

    // Check if all keys are the same (no additions/removals)
    let keysMatch = leftKeys.every(k => rightKeys.includes(k))

    if (!keysMatch && !convertedToObject) {
        // Keys differ (some added, some removed) - consolidate
        if (path !== '') {
            diffs[path] = dataGet(rootRight, path)
            return { changed: true, consolidated: true }
        }
    }

    // Recursively diff children
    let childDiffs = {}
    let changedCount = 0
    let consolidatedCount = 0
    let totalChildren = rightKeys.length

    rightKeys.forEach(key => {
        let childPath = path === '' ? key : `${path}.${key}`
        let result = diffRecursive(left[key], right[key], childPath, childDiffs, rootLeft, rootRight)
        if (result.changed) changedCount++
        if (result.consolidated) consolidatedCount++
    })

    // If all children changed AND none of them were already consolidated, consolidate to this level
    // (unless we're at root). This prevents double-consolidation up the tree.
    // ALSO skip consolidation if we converted to object - we're adding new items, not replacing
    // Only consolidate if there are MULTIPLE children - single property changes should remain granular
    // for wire:target to work correctly (e.g., wire:target="form.text" needs "form.text" not "form")
    if (path !== '' && totalChildren > 1 && changedCount === totalChildren && consolidatedCount === 0 && !convertedToObject) {
        diffs[path] = dataGet(rootRight, path)
        return { changed: true, consolidated: true }
    }

    // Otherwise, add individual child diffs
    Object.assign(diffs, childDiffs)

    // If any child was consolidated, bubble that up to prevent further consolidation
    return { changed: changedCount > 0, consolidated: consolidatedCount > 0 }
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

    let elWithNonce = document.querySelector('style[data-livewire-style][nonce]')

    if (elWithNonce) {
        nonce = elWithNonce.nonce

        return nonce
    }

    return null
}

/**
 * Livewire's base URL for loading JS/CSS modules.
 * Returns a full URL including origin, generated by PHP's url() helper.
 */
export function getModuleUrl() {
    return document.querySelector('[data-module-url]')?.getAttribute('data-module-url') ?? window.livewireScriptConfig['moduleUrl'] ?? null
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

export function walkUpwards(el, callback) {
    let current = el

    while (current) {
        let stop = undefined

        callback(current, { stop: (value) => value === undefined ? stop = current : stop = value })

        if (stop !== undefined) return stop

        if (current._x_teleportBack) current = current._x_teleportBack

        current = current.parentElement
    }
}

export function walkBackwards(el, callback) {
    let current = el

    while (current) {
        let stop = undefined

        callback(current, { stop: (value) => value === undefined ? stop = current : stop = value })

        if (stop !== undefined) return stop

        current = current.previousSibling
    }
}
