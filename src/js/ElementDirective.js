const prefix = require('./Prefix.js')()

export default class {
    constructor(type, modifiers, rawName, el) {
        this.type = type
        this.modifiers = modifiers
        this.rawName = rawName
        this.el = el
    }

    get value() {
        return this.el.getAttribute(this.rawName)
    }
}
