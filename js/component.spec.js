import { describe, it, expect } from 'vitest'
import Alpine from 'alpinejs'
import { Component } from '@/component'

window.Alpine = Alpine

describe('Component', () => {
    it('keeps a deferred update written while a request that sent the same object is in flight', () => {
        let component = createComponent({ answers: [{ name: 'Ann' }, { s: 'arr' }] })

        // A live update adds a key, so the whole "answers" object is sent...
        component.reactive.answers.a = 'one'

        let updates = component.getUpdates()

        expect(updates).toEqual({ answers: { name: 'Ann', a: 'one' } })

        // A deferred update is written to the same object while the request is in flight...
        component.reactive.answers.b = 'two'

        component.mergeNewSnapshot(JSON.stringify({
            data: { answers: [{ name: 'Ann', a: 'one' }, { s: 'arr' }] },
            memo: { id: 'abc', name: 'test' },
        }), {}, updates)

        expect(component.reactive.answers).toEqual({ name: 'Ann', a: 'one', b: 'two' })
        expect(component.getUpdates()).toEqual({ answers: { name: 'Ann', a: 'one', b: 'two' } })
    })

    it('keeps a deferred update written to an empty array while a request is in flight', () => {
        let component = createComponent({ items: [[], { s: 'arr' }] })

        // A live update adds a string key to the empty array...
        component.reactive.items.a = 'one'

        let updates = component.getUpdates()

        expect(updates).toEqual({ 'items.a': 'one' })

        // A deferred update is written to the same array while the request is in flight...
        component.reactive.items.b = 'two'

        // The server returns the array as an object, as PHP does for string keys...
        component.mergeNewSnapshot(JSON.stringify({
            data: { items: [{ a: 'one' }, { s: 'arr' }] },
            memo: { id: 'abc', name: 'test' },
        }), {}, updates)

        expect({ ...component.reactive.items }).toEqual({ a: 'one', b: 'two' })
        expect(component.getUpdates()).toEqual({ items: { a: 'one', b: 'two' } })
    })
})

function createComponent(data) {
    let el = document.createElement('div')

    el.setAttribute('wire:id', 'abc')
    el.setAttribute('wire:snapshot', JSON.stringify({ data, memo: { id: 'abc', name: 'test' } }))
    el.setAttribute('wire:effects', JSON.stringify({}))

    return new Component(el)
}
