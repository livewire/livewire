import { closestComponent } from "../lifecycle";
import { state } from "../state";
import { on } from './../../../synthetic/js/index'

export default function () {
    on('effects', (target, effects, path) => {
        queueMicrotask(() => {
            let files = effects.hotReload

            if (! files) return

            let component = state.components[target.__livewireId]

            if (files) {
                files.forEach(file => {
                    whenFileIsModified(file, () => {
                        component.$wire.$refresh()
                    })
                })
            }
        })
    })

    let es = new EventSource("/livewire/hot-reload")

    es.addEventListener("message", function(event) {
        let data = JSON.parse(event.data)

        data.file && console.log(data.file, listeners)

        if (data.file && listeners[data.file]) {
            listeners[data.file].forEach(cb => cb())
        }
    })

    es.onerror = function(err) {
        console.log("EventSource failed:", err)
    }

    es.onopen = function(err) {
        console.log("opened", err)
    }
}

let listeners = []

function whenFileIsModified(file, callback) {
    if (! listeners[file]) listeners[file] = []

    listeners[file].push(callback)
}
