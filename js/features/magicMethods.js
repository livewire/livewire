import { findComponent } from '../state'
import { closestComponent } from '../lifecycle'
import { on } from './../synthetic/index'

on('decorate', (target, path, addProp, decorator, symbol) => {
    addProp('$set', (...params) => {
        let component = findComponent(target.__livewireId)

        return component.$wire.set(...params)
    })

    addProp('$toggle', (name) => {
        let component = findComponent(target.__livewireId)

        return component.$wire.set(name, ! component.$wire.get(name))
    })
})
