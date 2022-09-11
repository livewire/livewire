import { on } from './../../../synthetic/js/index'
import { findComponent } from '../state'
import { closestComponent } from '../lifecycle'

export default function () {
    on('decorate', (target, path, addProp, decorator, symbol) => {
        addProp('$parent', { get() {
            let component = findComponent(target.__livewireId)

            let parent = closestComponent(component.el.parentElement)

            return parent.$wire
        }})
    })
}
