import { fireEvent, wait } from 'dom-testing-library'
import { mount, mountAsRoot } from './utils'
const timeout = ms => new Promise(resolve => setTimeout(resolve, ms))

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

test('two keydown events', async () => {
    var payload
    mount('<button wire:keydown="someMethod" wire:keydown.enter="otherMethod"></button>', i => payload = i)

    fireEvent.keyDown(document.querySelector('button'), { key: 'Enter' })

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
        expect(payload.actionQueue[0].payload.params).toEqual([])
        expect(payload.actionQueue[1].type).toEqual('callMethod')
        expect(payload.actionQueue[1].payload.method).toEqual('otherMethod')
        expect(payload.actionQueue[1].payload.params).toEqual([])
    })
})

test('keydown.enter doesnt fire when other keys are pressed', async () => {
    var payload
    mount('<button wire:keydown.enter="otherMethod"></button>', i => payload = i)

    fireEvent.keyDown(document.querySelector('button'), { key: 'Escape' })

    await timeout(10)

    expect(payload).toBeUndefined()
})

test('keyup.enter doesnt fire when other keys are pressed', async () => {
    var payload
    mount('<button wire:keyup.enter="otherMethod"></button>', i => payload = i)

    fireEvent.keyUp(document.querySelector('button'), { key: 'Escape' })

    await timeout(10)

    expect(payload).toBeUndefined()
})

test('polling is disabled if livewire is offline', async () => {
    var pollHappened = false
    mount('<div wire:poll.50ms="someMethod"></div>', () => { pollHappened = true })
    window.livewire.components.livewireIsOffline = true

    await timeout(59)

    expect(pollHappened).toBeFalsy()

    // Reset state for other tests.
    window.livewire.components.livewireIsOffline = false
})

test('polling without specifying method refreshes by default', async () => {
    var pollPayload;
    mount('<div wire:poll.50ms></div>', (i) => { pollPayload = i })

    await timeout(49)

    expect(pollPayload).toBeUndefined()

    await timeout(10)

    expect(pollPayload.actionQueue[0].payload.method).toEqual('$refresh')
})

test('polling on root div', async () => {
    var pollHappened = false
    mountAsRoot('<div wire:id="123" wire:data="{}" wire:poll.50ms="someMethod"></div>', () => { pollHappened = true })

    await timeout(49)

    expect(pollHappened).toBeFalsy()

    await timeout(10)

    expect(pollHappened).toBeTruthy()
})

test('polling is disabled if ', async () => {
    var pollHappened = false
    mountAsRoot('<div wire:id="123" wire:data="{}" wire:poll.50ms="someMethod"></div>', () => { pollHappened = true })

    await timeout(49)

    expect(pollHappened).toBeFalsy()

    await timeout(10)

    expect(pollHappened).toBeTruthy()
})

test('init', async () => {
    var initHappened = false
    mountAsRoot('<div wire:id="123" wire:data="{}" wire:init="someMethod"></div>', () => { initHappened = true })

    await timeout(10)

    expect(initHappened).toBeTruthy()
})
