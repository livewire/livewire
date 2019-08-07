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

test('new components are discovered in the dom with rescan', async () => {
    const element = mount(`<div><button wire:click="foo"></button></div>`)

    // Add a new component to the DOM manually.
    const newComponentEl = document.createElement('div')
    newComponentEl.innerHTML = `<div wire:id="456" wire:initial-data="{}"></div>`
    element.parentElement.appendChild(newComponentEl.firstElementChild)

    window.livewire.rescan()

    expect(window.livewire.components.components().length).toBe(2)
})

test('rescanned components dont register twice', async () => {
    var payloads = []
    mount('<button wire:click="someMethod"></button>', i => payloads.push(i))

    window.livewire.rescan()

    document.querySelector('button').click()

    await timeout(5)

    expect(payloads.length).toEqual(1)
})
