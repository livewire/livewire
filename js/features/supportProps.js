import { findComponent } from "../store";
import { on } from '@/events'

on('commit.prepare', (component) => {
    component.children.forEach(child => {
        let childMeta = child.snapshot.memo
        let props = childMeta.props

        // If this child has a prop from the parent
        if (props) child.$wire.$commit()
    })
})
