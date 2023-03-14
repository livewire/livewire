import { findComponent } from "../store";
import { on } from '@/events'

on('request.prepare', (component) => {
    let meta = component.snapshot.memo
    let childIds = Object.values(meta.children).map(i => i[1])

    childIds.forEach((id) => {
        let child = findComponent(id)
        let childMeta = child.snapshot.memo
        let props = childMeta.props

        // If this child has a prop from the parent
        if (props) child.$wire.$commit()
    })
})
