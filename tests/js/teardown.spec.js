import { mount } from './utils'
const timeout = ms => new Promise(resolve => setTimeout(resolve, ms))

test('event listeners are removed on teardown', async () => {
    var payload
    mount('<button wire:click="someMethod"></button>', i => payload = i)

    window.livewire.stop()

    document.querySelector('button').click()

    await timeout(5)

    expect(payload).toBeUndefined()
})
