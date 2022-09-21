import { dataGet, each } from "../utils"
import { on } from "../events"
import { callMethod } from "./../index"

export default function () {
    // Normal, request-triggering, methods...
    on('decorate', (target, path, addProp, decorator, symbol) => {
        let effects = target.effects[path]

        if (! effects) return

        let methods = effects['methods'] || []

        methods.forEach(method => {
            addProp(method, async (...params) => {
                // If this method is passed directly to a Vue or Alpine
                // event listener (@click="someMethod") without using
                // parens, strip out the automatically added event.
                if (params.length === 1 && params[0] instanceof Event) {
                    params = []
                }

                return await callMethod(symbol, path, method, params)
            })
        })
    })

    // Pure "js" methods...
    on('decorate', (target, path, addProp) => {
        let effects = target.effects[path]

        if (! effects) return

        let methods = effects['js'] || []

        let AsyncFunction = Object.getPrototypeOf(async function(){}).constructor

        each(methods, (name, expression) => {
            let func = new AsyncFunction([], expression)

            addProp(name, () => {
                func.bind(dataGet(target.reactive, path))()
            })
        })
    })
}
