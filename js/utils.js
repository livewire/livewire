
export function debounce(func, wait, immediate) {
    var timeout

    return function () {
        var context = this,
            args = arguments
        var later = function () {
            timeout = null
            if (!immediate) func.apply(context, args)
        }
        var callNow = immediate && !timeout
        clearTimeout(timeout)
        timeout = setTimeout(later, wait)
        if (callNow) func.apply(context, args)
    }
}

export function throttle(func, wait) {
    let inThrottle

    return function () {
        const args = arguments
        const context = this

        if (! inThrottle) {
            func.apply(context, args)

            inThrottle = true

            setTimeout(() => inThrottle = false, wait)
        }
    }
}

export function dataGet(object, key) {
    return key.split('.').reduce((carry, i) => {
        if (carry === undefined) return undefined

        return carry[i]
    }, object)
}

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

export function decorate(object, decorator) {
    return new Proxy(object, {
        get(target, property) {
            console.log('access property', property)
            if (property in decorator) {
                return decorator[property]
            } else if (property in target) {
                return target[property]
            } else if ('__get' in decorator && ! ['then'].includes(property)) {
                return decorator.__get(property)
            }
        },

        set(target, property, value) {
            if (property in decorator) {
                decorator[property] = value
            } else if (property in target) {
                target[property] = value
            } else if ('__set' in decorator && ! ['then'].includes(property)) {
                decorator.__set(property, value)
            }
        },
    })
}

export function tap(thing, callback) {
    callback(thing)

    return thing
}

export class Bag {
    constructor() { this.arrays = {} }

    add(key, value) {
        if (! this.arrays[key]) this.arrays[key] = []
        this.arrays[key].push(value)
    }

    get(key) { return this.arrays[key] || [] }

    each(key, callback) { return this.get(key).forEach(callback) }
}

export class WeakBag {
    constructor() { this.arrays = new WeakMap }

    add(key, value) {
        if (! this.arrays.has(key) ) this.arrays.set(key, [])
        this.arrays.get(key).push(value)
    }

    get(key) { return this.arrays.has(key) ? this.arrays.get(key) : [] }

    each(key, callback) { return this.get(key).forEach(callback) }
}

export function monkeyPatchDomSetAttributeToAllowAtSymbols() {
    // Because morphdom may add attributes to elements containing "@" symbols
    // like in the case of an Alpine `@click` directive, we have to patch
    // the standard Element.setAttribute method to allow this to work.
    let original = Element.prototype.setAttribute

    let hostDiv = document.createElement('div')

    Element.prototype.setAttribute = function newSetAttribute(name, value) {
        if (! name.includes('@')) {
            return original.call(this, name, value)
        }

        hostDiv.innerHTML = `<span ${name}="${value}"></span>`

        let attr = hostDiv.firstElementChild.getAttributeNode(name)

        hostDiv.firstElementChild.removeAttributeNode(attr)

        this.setAttributeNode(attr)
    }
}

export function dispatch(el, name, detail = {}, bubbles = true) {
    el.dispatchEvent(
        new CustomEvent(name, {
            detail,
            bubbles,
            // Allows events to pass the shadow DOM barrier.
            composed: true,
            cancelable: true,
        })
    )
}

