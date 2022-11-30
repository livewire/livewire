import { findComponent } from "../state";
import { on } from './../synthetic/index'

export default function () {
    on('request.before', (target) => {
        let meta = target.snapshot.data[1]
        let childIds = Object.values(meta.children).map(i => i[1])

        childIds.forEach((id) => {
            let child = findComponent(id)
            let childSynthetic = child.synthetic
            let childMeta = childSynthetic.snapshot.data[1]
            let props = childMeta.props

            // If this child has a prop from the parent
            if (props) childSynthetic.ephemeral.$commit()
        })
    })
}
