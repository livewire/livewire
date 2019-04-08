import { wait } from 'dom-testing-library'
import { mount } from './utils'

test('basic click', async () => {
    var payload
    mount('<button wire:click="someMethod"></button>', i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
        expect(payload.actionQueue[0].payload.params).toEqual([])
    })
})

test('click with params', async () => {
    var payload
    mount(`<button wire:click="someMethod('foo', 'bar')"></button>`, i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})

test('if a click and blur happen at the same time, the actions are queued and sent together', async () => {
    var payload
    mount('<input wire:blur="onBlur"><button wire:click="onClick"></button>', i => payload = i)

    document.querySelector('input').focus()
    document.querySelector('button').click()
    document.querySelector('input').blur()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('onClick')
        expect(payload.actionQueue[1].type).toEqual('callMethod')
        expect(payload.actionQueue[1].payload.method).toEqual('onBlur')
    })
})
