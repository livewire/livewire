import debounce from './debounce.js'
const prefix = require('./prefix.js')()

export default {
    // This is soooo bad, but it currently get's set inside "debounce"
    timeout: 0,

    // Eff me, this function prevents some weird front-end behavior.
    // It's too complicated for me to go into detail right now.
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

    getRoot(component) {
        return document.querySelector(`[${prefix}\\:root="${component}"]`)
    },

    get livewireElements() {
        let hold = [];
        var tags = document.evaluate(`//*[@*[starts-with(name(), "${prefix}")]]`, document, null, XPathResult.UNORDERED_NODE_ITERATOR_TYPE, null)

        var node = tags.iterateNext()

        while (node) {
            hold.push(node)
            node = tags.iterateNext()
        }

        return hold
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

    attachEnter(el, callback) {
        el.addEventListener('keydown', e => {
            if (e.keyCode == '13') {
                const { method, params } = this.parseOutMethodAndParams(el.getAttribute(`${prefix}:keydown.enter`))
                this.debounceOnTimeout(callback)(method, params, e.target)
            }
        })
    },

    attachSync(el, callback) {
        el.addEventListener('input', debounce(e => {
            const model = e.target.getAttribute(`${prefix}:sync`)
            callback(model, e.target)
        }, 250))
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
