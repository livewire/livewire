import { findComponent } from "../state";
import { on } from './../synthetic/index'

on('target.request.prepare', (target) => {
    let meta = target.snapshot.memo
    let childIds = Object.values(meta.children).map(i => i[1])

    childIds.forEach((id) => {
        let child = findComponent(id)
        let childSynthetic = child.synthetic
        let childMeta = childSynthetic.snapshot.memo
        let props = childMeta.props

        // If this child has a prop from the parent
        if (props) childSynthetic.ephemeral.$commit()
    })
})
