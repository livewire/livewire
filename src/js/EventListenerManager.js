import debounce from './Debounce.js'
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

    attachClick(el, callback, modifiers, value) {
        el.addEventListener('click', (e => {
            if (modifiers.includes('prevent')) {
                e.preventDefault()
            }

            if (modifiers.includes('stop')) {
                e.stopPropagation()
            }

            if (value) {
                const { method, params } = this.parseOutMethodAndParams(value)
                this.debounceOnTimeout(callback)(method, params, e.target)
            }
        }))
    },

    attachSubmit(el, callback) {
        el.addEventListener('submit', e => {
            e.preventDefault()

            const { method, params } = this.parseOutMethodAndParams(el.getAttribute(`${prefix}:submit`))

            this.debounceOnTimeout(callback)(method, params, e.target)
        })
    },

    attachEnter(el, callback, modifiers, value) {
        el.addEventListener('keydown', e => {
            if (modifiers.length === 0) {
                const { method, params } = this.parseOutMethodAndParams(value)
                this.debounceOnTimeout(callback)(method, params, e.target)
            }

            if (modifiers.includes(e.key.split(/[_\s]/).join("-").toLowerCase())) {
                const { method, params } = this.parseOutMethodAndParams(value)
                this.debounceOnTimeout(callback)(method, params, e.target)
            }
        })
    },

    attachSync(el, callback) {
        el.addEventListener('input', debounce(e => {
            const directives = new ElementDirectives(e.target)
            const model = directives.get('model').value
            callback(model, e.target)
        }, 150))
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
    }
}
