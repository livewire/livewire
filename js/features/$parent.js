import { on } from '../synthetic/index'
import { findComponent } from '../state'
import { closestComponent } from '../lifecycle'

on('decorate', (target, path, addProp, decorator, symbol) => {
    let memo

    addProp('$parent', { get() {
        if (memo) return memo.$wire

        let component = findComponent(target.__livewireId)

        let parent = closestComponent(component.el.parentElement)

        memo = parent

        return parent.$wire
    }})
})
