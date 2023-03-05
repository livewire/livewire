import { findComponent } from "../state";
import { on } from './../synthetic/index'

on('target.request.prepare', (target) => {
    let meta = target.snapshot.memo
    let childIds = Object.values(meta.children).map(i => i[1])

    childIds.forEach((id) => {
        let child = findComponent(id)
        let childSynthetic = child.synthetic
        let childMeta = childSynthetic.snapshot.memo
        let bindings = childMeta.bindings

        // If this child has a binding from the parent
        if (bindings) childSynthetic.ephemeral.$commit()
    })
})
