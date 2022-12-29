import { directives } from '../directives'
import { on } from './../synthetic/index'
import { findComponent } from 'state'

export default function () {
    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('model')) return

        let directive = allDirectives.get('model')

        on('target.request', (target) => {
            let targetComponent = findComponent(target.__livewireId)

            if (component !== targetComponent) return

            return () => {
                if (target.effects[''].dirty) {
                    if (target.effects[''].dirty.includes(directive.value)) {
                        el._x_forceModelUpdate()
                    }
                }
            }
        })
    })
}
