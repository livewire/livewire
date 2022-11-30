import { each } from "../utils"
import { on } from "../events"

export default function () {
    on('decorate', (target, path, addProp, decorator, symbol) => {
        addProp('$poll', (callback) => {
            syncronizedInterval(2500, () => {
                callback()

                target.ephemeral.$commit()
            })
        })
    })
}

let clocks = []

function syncronizedInterval(ms, callback) {
    if (! clocks[ms]) {
        let clock = {
            timer: setInterval(() => each(clock.callbacks, (key, value) => value()), ms),
            callbacks: [],
        }

        clocks[ms] = clock
    }

    clocks[ms].callbacks.push(callback)
}
