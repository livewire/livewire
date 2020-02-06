
export default class {
    constructor(type, modifiers, rawName, el) {
        this.type = type
        this.modifiers = modifiers
        this.rawName = rawName
        this.el = el
        this.eventContext
    }

    setEventContext(context) {
        this.eventContext = context
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
        let durationInMilliSeconds
        const durationInMilliSecondsString = this.modifiers.find(mod => mod.match(/([0-9]+)ms/))
        const durationInSecondsString = this.modifiers.find(mod => mod.match(/([0-9]+)s/))

        if (durationInMilliSecondsString) {
            durationInMilliSeconds = Number(durationInMilliSecondsString.replace('ms', ''))
        } else if (durationInSecondsString){
            durationInMilliSeconds = Number(durationInSecondsString.replace('s', '')) * 1000
        }

        return durationInMilliSeconds || defaultDuration
    }

    parseOutMethodAndParams(rawMethod) {
        let method = rawMethod
        let params = []
        const methodAndParamString = method.match(/(.*?)\((.*)\)/)

        if (methodAndParamString) {
            // This "$event" is for use inside the livewire event handler.
            const $event = this.eventContext
            method = methodAndParamString[1]
            params = methodAndParamString[2].split(/ ?, ?(?=(?:[^"'`\[\]]|'[^'\\]*(?:\\.[^'\\]*)*'|"[^"\\]*(?:\\.[^"\\]*)*"|`[^`\\]*(?:\\.[^`\\]*)*`|\[[^\]]*\])*$)/).filter(Boolean).map(param => eval(param))
        }

        return { method, params }
    }

    cardinalDirectionOr(fallback = 'right') {
        if (this.modifiers.includes('up')) return 'up'
        if (this.modifiers.includes('down')) return 'down'
        if (this.modifiers.includes('left')) return 'left'
        if (this.modifiers.includes('right')) return 'right'
        return fallback
    }
}
