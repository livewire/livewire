import { wait } from 'dom-testing-library'
import { mount, mountAndReturn } from './utils'

test('click inside nested component is assigned to nested component', async () => {
    var payload
    mount(`<div>
        <div wire:id="456" wire:initial-data="{}">
            <button wire:click="someMethod"></button>
        </div>
    </div>`, i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.id).toEqual('456')
    })
})

test('added component gets initialized', async () => {
    var payload
    mountAndReturn(`<div><button wire:click="foo"></button></div>`,
    `<div>
        <button wire:click="foo"></button>
        <div wire:id="456" wire:initial-data="{}">
            <button wire:click="bar"></button>
        </div>
    </div>`, [], i => payload = i)

    document.querySelector('button[wire\:click="foo"]').click()

    await wait(() => {
        expect(payload.id).toEqual('123')
    })

    document.querySelector('button[wire\:click="bar"]').click()

    await wait(() => {
        expect(payload.id).toEqual('456')
    })
})

test('component placeholder gets ignored', async () => {
    var payload
    mountAndReturn(
    `<div>
        <button wire:click="foo"></button>
        <div wire:id="456" wire:initial-data="{}">
            <button wire:click="bar"></button>
        </div>
    </div>`,
    `<div>
        <button wire:click="foo"></button>
        <div wire:id="456" wire:ignore></div>
    </div>`,
   [], i => payload = i)

    document.querySelector('button[wire\:click="foo"]').click()

    await wait(() => {
        expect(payload.id).toEqual('123')
    })

    document.querySelector('button[wire\:click="bar"]').click()

    await wait(() => {
        expect(payload.id).toEqual('456')
    })
})
