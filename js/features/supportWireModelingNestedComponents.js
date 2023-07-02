import { findComponent } from "../store";
import { on } from '@/events'

on('commit.prepare', (component) => {
    component.children.forEach(child => {
        let childMeta = child.snapshot.memo
        let bindings = childMeta.bindings

        // If this child has a binding from the parent
        if (bindings) child.$wire.$commit()
    })
})

