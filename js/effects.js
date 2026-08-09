export class Effects {
    constructor(data = {}) {
        Object.assign(this, data && typeof data === 'object' ? data : {})
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
