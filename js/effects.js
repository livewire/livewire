export class Effects {
    constructor(data = {}) {
        let source = isObject(data) ? data : {}

        for (let key of Object.keys(source)) {
            if (! isReservedKey(key)) this[key] = source[key]
        }
    }

    static from(data) {
        return data instanceof Effects ? data : new Effects(data)
    }

    has(key) {
        return Object.prototype.hasOwnProperty.call(this, key)
    }

    hasValue(key) {
        return this.has(key) && !! this[key]
    }

    toJSON() {
        return { ...this }
    }
}

function isObject(source)
{
    return source !== null && typeof source === 'object' && ! Array.isArray(source)
}

function isReservedKey(key)
{
    return key === 'has' || key === 'hasValue' || key === 'toJSON'
}
