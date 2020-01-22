import {fireEvent, wait} from 'dom-testing-library'
import {mount} from "./utils";

test('without space', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo','bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})

test('with space before', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo' ,'bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})


test('with space after', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo', 'bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})


test('with space around', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo' , 'bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})
