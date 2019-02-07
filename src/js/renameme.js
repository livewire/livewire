import debounce from 'debounce'
const prefix = require('./prefix.js')()

export default {
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

    attachClick(el, callback) {
        el.addEventListener('click', e => {
            const { method, params } = this.parseOutMethodAndParams(el.getAttribute(`${prefix}:click`))
            callback(method, params, e.target)
        })
    },

    attachSubmit(el, callback) {
        el.addEventListener('submit', e => {
            e.preventDefault()

            const { method, params } = this.parseOutMethodAndParams(el.getAttribute(`${prefix}:submit`))

            callback(method, params, e.target)
        })
    },

    attachEnter(el, callback) {
        el.addEventListener('keydown', e => {
            if (e.keyCode == '13') {
                const { method, params } = this.parseOutMethodAndParams(el.getAttribute(`${prefix}:keydown.enter`))
                callback(method, params, e.target)
            }
        })
    },

    attachSync(el, callback) {
        el.addEventListener('input', debounce(e => {
            const model = el.getAttribute(`${prefix}:sync`)
            callback(model, el)
        }, 200))
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
