import { morph } from "../morph";
import { findComponent } from "../state";
import { on } from './../../../synthetic/js/index'

export default function () {
    on('effects', (target, effects, path) => {
        let html = effects.html
        if (! html) return

        let component = findComponent(target.__livewireId)

        // Doing this so all the state of components in a nested tree has a chance
        // to update on synthetic's end. (mergeSnapshots kinda deal).
        queueMicrotask(() => {
            morph(component, component.el, html)
        })
    })
}



