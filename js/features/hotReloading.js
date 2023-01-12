import { findComponent, state } from "@/state";
import { on } from '@synthetic/index'

// // Only allow this in "Simple Preview"...
// if (! navigator.userAgent.includes('Electron')) return

// if (! enabled.includes('hot-reloading')) return

on('effects', (target, effects) => {
    queueMicrotask(() => {
        let files = effects.hotReload

        if (! files) return

        let component = findComponent(target.__livewireId)

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

    if (data.file && listeners[data.file]) {
        listeners[data.file].forEach(cb => cb())
    }
})

es.onerror = function(err) {
    // console.log("EventSource failed:", err)
}

es.onopen = function(err) {
    // console.log("opened", err)
}

let listeners = []

function whenFileIsModified(file, callback) {
    if (! listeners[file]) listeners[file] = []

    listeners[file].push(callback)
}
