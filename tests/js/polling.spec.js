import { mount, mountAsRoot, mountAsRootAndReturn } from './utils'
const timeout = ms => new Promise(resolve => setTimeout(resolve, ms))

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
        () => {
            pollCount++
        }
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
        () => {
            pollCount++
        }
    )

    document.querySelector('button').click()

    await timeout(49)
    expect(pollCount).toBe(1)

    await timeout(55)
    expect(pollCount).toBe(2)
})

test('polling on root div', async () => {
    var pollHappened = false
    mountAsRoot(
        '<div wire:id="123" wire:initial-data="{}" wire:poll.50ms="someMethod"></div>',
        () => {
            pollHappened = true
        }
    )

    await timeout(49)

    expect(pollHappened).toBeFalsy()

    await timeout(10)

    expect(pollHappened).toBeTruthy()
})

test('polling is disabled if ', async () => {
    var pollHappened = false
    mountAsRoot(
        '<div wire:id="123" wire:initial-data="{}" wire:poll.50ms="someMethod"></div>',
        () => {
            pollHappened = true
        }
    )

    await timeout(49)

    expect(pollHappened).toBeFalsy()

    await timeout(10)

    expect(pollHappened).toBeTruthy()
})
