import { directive, getDirectives } from '@/directives'
import { findComponent } from '@/state'
import { on } from '@synthetic/index'

directive('model', (el, { expression }, { component }) => {
    on('target.request', (target) => {
        let targetComponent = findComponent(target.__livewireId)

        if (component !== targetComponent) return

        return () => {
            if (target.effects.dirty) {
                if (target.effects.dirty.includes(expression)) {
                    el._x_forceModelUpdate(
                        component.$wire.get(expression, false)
                    )
                }
            }
        }
    })
})
