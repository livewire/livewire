import { findComponent } from "../store";
import { on } from '@/events'

on('commit.prepare', ({ component }) => {
    // Bind all wire:modeled children to parent requests...
    component.children.forEach(child => {
        if (hasBindings(child)) {
            child.$wire.$commit()
        }
    })
})

function hasBindings(component) {
    let childMemo = component.snapshot.memo
    let bindings = childMemo.bindings

    return !! bindings
}

