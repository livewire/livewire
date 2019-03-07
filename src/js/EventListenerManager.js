import ElementDirectives from './ElementDirectives.js';
const prefix = require('./Prefix.js')()

export default {
    // This is soooo bad, but it currently get's set inside "./Debounce.js"
    timeout: 0,

    // Eff me, this function prevents some weird front-end behavior.
    // It's too complicated for me to go into detail right now, sorry everyone.
    debounceOnTimeout(callback) {
        var outerContext = this
        return function () {
            var context = this, args = arguments;
            if (outerContext.timeout > 0) {
                setTimeout(() => {
                    callback.apply(context, args);
                 }, outerContext.timeout)
            } else {
                callback.apply(context, args);
            }
        }
    },

    attachSubmit(el, callback) {
    },

    attachEnter(el, callback, modifiers, value) {
    },

    attachSync(el, callback) {
    },

    parseOutMethodAndParams(rawMethod) {
        let params = []
        let method = rawMethod

        if (method.match(/(.*)\((.*)\)/)) {
            const matches = method.match(/(.*)\((.*)\)/)
            method = matches[1]
            params = matches[2].split(', ').map(param => {
                if (eval('typeof ' + param) === 'undefined') {
                    return document.querySelector(`[${prefix}\\:model="` + param + '"]').value
                }

                return eval(param)
            })
        }

        return { method, params }
    },

    attachEvent(event) {

    },

    preventOrStop(event, modifiers) {
        if (modifiers.includes('prevent')) {
            event.preventDefault()
        }

        if (modifiers.includes('stop')) {
            event.stopPropagation()
        }
    }
}
