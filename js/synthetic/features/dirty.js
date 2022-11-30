import { dataGet, deeplyEqual } from "../utils"
import { reactive } from "../index"
import { on } from "../events"

export default function () {
    on('new', target => {
        target.__dirty = reactive({ state: 0 })
    })

    on('target.request', (target, payload) => {
        return () => target.__dirty.state = +new Date()
    })

    on('decorate', (target, path) => {
        return decorator => {
            Object.defineProperty(decorator, '$dirty', { get() {
                let throwaway = target.__dirty.state

                let thing1 = dataGet(target.canonical, path)
                let thing2 = dataGet(target.reactive, path)

                return ! deeplyEqual(thing1, thing2)
            }})

            return decorator
        }
    })
}
