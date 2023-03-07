import { findComponent } from "../store";
import { on } from '@/events'

on('request.prepare', (component) => {
    let meta = component.snapshot.memo
    let childIds = Object.values(meta.children).map(i => i[1])

    childIds.forEach((id) => {
        let child = findComponent(id)
        let childMeta = child.snapshot.memo
        let bindings = childMeta.bindings

        // If this child has a binding from the parent
        if (bindings) child.ephemeral.$commit()
    })
})
