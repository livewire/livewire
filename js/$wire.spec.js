import { describe, it, expect, vi } from 'vitest'
import Alpine from 'alpinejs'
import { Component } from './component'
import { generateWireObject } from './$wire'

function createComponent(data = {}) {
    let component = Object.create(Component.prototype)

    component.reactive = Alpine.reactive(data)
    component.cleanups = []
    component.$wire = generateWireObject(component, component.reactive)

    return component
}

describe('$wire', () => {
    it('removes its component cleanup when a watcher is manually unwatched', () => {
        let component = createComponent({ someProperty: 0 })

        let unwatch = component.$wire.$watch('someProperty', vi.fn())

        expect(component.cleanups).toHaveLength(1)

        unwatch()

        expect(component.cleanups).toHaveLength(0)
    })
})
