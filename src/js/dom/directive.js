
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

    get method() {
        const { method } =  this.parseOutMethodAndParams(this.value)

        return method
    }

    get params() {
        const { params } =  this.parseOutMethodAndParams(this.value)

        return params
    }

    durationOr(defaultDuration) {
        const durationAsString = this.modifiers.find(mod => mod.match(/(.*)ms/))

        return durationAsString
            ? Number(durationAsString.replace('ms', ''))
            : defaultDuration
    }

    parseOutMethodAndParams(rawMethod) {
        let method = rawMethod
        let params = []
        const methodAndParamString = method.match(/(.*)\((.*)\)/)

        if (methodAndParamString) {
            method = methodAndParamString[1]
            params = methodAndParamString[2].split(', ').map(param => eval(param))
        }

        return { method, params }
    }
}
