import { reactive } from ".."
import { on } from "../events"

export default function () {
    on('new', (target, path) => {
        target.__errors = reactive({ state: [] })
    })

    on('decorate', (target, path) => {
        return decorator => {
            Object.defineProperty(decorator, '$errors', { get() {
                let errors = {}

                Object.entries(target.__errors.state).forEach(([key, value]) => {
                    errors[key] = value[0]
                })

                return errors
            }})

            return decorator
        }
    })

    on('effects', (target, effects, path) => {
        let errors = effects['errors'] || []

        target.__errors.state = errors
    })
}
