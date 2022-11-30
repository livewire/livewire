import { on } from "../events"

export default function () {
    on('effects', (target, effects) => {
        if (! effects['redirect']) return

        let url = effects['redirect']

        window.location.href = url
    })
}

