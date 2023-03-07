import { directive, getDirectives } from '@/directives'
import { findComponent } from '@/store'
import { on } from '@/events'

directive('model', (el, { expression }, { component }) => {
    on('request', (iComponent) => {
        if (iComponent !== component) return

        return () => {
            let dirty = component.effects.dirty

            if (! dirty) return

            if (isDirty(expression, dirty)) {
                el._x_forceModelUpdate(
                    component.$wire.get(expression, false)
                )
            }
        }
    })
})

function isDirty(subject, dirty) {
    // Check for exact match: wire:model="bob" in ['bob']
    if (dirty.includes(subject)) return true

    // Check case of parent: wire:model="bob.1" in ['bob']
    return dirty.some(i => subject.startsWith(i))
}
