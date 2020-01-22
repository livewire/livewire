import { wait } from 'dom-testing-library'
import { mountAndReturn } from './utils'

test('beforeDomUpdate hook', async () => {
    var hookWasCalled = false;

    mountAndReturn('<button wire:click="onClick"></button>', '<div id="foo"></div>')

    window.livewire.hook('beforeDomUpdate', () => {
        hookWasCalled = true
        expect(document.querySelector('#foo')).toBeFalsy()
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(hookWasCalled).toBeTruthy()
        expect(document.querySelector('#foo')).toBeTruthy()
    })
})

test('afterDomUpdate hook', async () => {
    var hookWasCalled = false;

    mountAndReturn('<button wire:click="onClick"></button>', '<div id="foo"></div>')

    window.livewire.hook('afterDomUpdate', () => {
        hookWasCalled = true
        expect(document.querySelector('#foo')).toBeTruthy()
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(hookWasCalled).toBeTruthy()
    })
})
