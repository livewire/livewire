import { fireEvent, wait } from 'dom-testing-library'
import { mount, mountAsRoot, mountAsRootAndReturn } from './utils'
const timeout = ms => new Promise(resolve => setTimeout(resolve, ms))

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

test('polling will stop if component is conditionally removed', async () => {
    // @todo: This assertion is hard to make given the current testing utilities.
    // Leaving this here so that we're aware of the need for it.
    expect(true).toBeTruthy()
})

test('polling will stop if directive is removed', async () => {
    var pollCount = 0

    mountAsRootAndReturn(
        '<div wire:id="123" wire:initial-data="{}" wire:poll.50ms="someMethod"></div>',
        '<div wire:id="123" wire:initial-data="{}"></div>',
        null,
        () => { pollCount++ }
    )

    await timeout(49) // 49ms
    expect(pollCount).toBe(0)

    await timeout(11) // 60ms
    expect(pollCount).toBe(1)

    // wire:poll is removed, the count remains 1
    await timeout(50) // 110ms
    expect(pollCount).toBe(1)
})

test('polling will start if directive is added', async () => {
    var pollCount = 0

    mountAsRootAndReturn(
        '<div wire:id="123" wire:initial-data="{}"><button wire:click="$refresh"></button></div>',
        '<div wire:id="123" wire:initial-data="{}" wire:poll.50ms="someMethod"><button wire:click="$refresh"></button></div>',
        null,
        () => { pollCount++ }
    )

    document.querySelector('button').click()

    await timeout(49)
    expect(pollCount).toBe(1)

    await timeout(55)
    expect(pollCount).toBe(2)
})

test('polling on root div', async () => {
    var pollHappened = false
    mountAsRoot('<div wire:id="123" wire:initial-data="{}" wire:poll.50ms="someMethod"></div>', () => { pollHappened = true })

    await timeout(49)

    expect(pollHappened).toBeFalsy()

    await timeout(10)

    expect(pollHappened).toBeTruthy()
})

test('polling is disabled if ', async () => {
    var pollHappened = false
    mountAsRoot('<div wire:id="123" wire:initial-data="{}" wire:poll.50ms="someMethod"></div>', () => { pollHappened = true })

    await timeout(49)

    expect(pollHappened).toBeFalsy()

    await timeout(10)

    expect(pollHappened).toBeTruthy()
})
