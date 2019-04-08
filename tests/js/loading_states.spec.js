import { wait } from 'dom-testing-library'
import { mount } from './utils'

test('add loading class to element during network requests', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading-class.add="foo"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('foo')).toBeTruthy()
    })
})

test('only add loading class when targeting the trigering element', async () => {
    mount(
`<button wire:ref="foo" wire:click="onClick"></button>
<span wire:loading-class.add="baz" wire:loading-target="foo"></span>
<h1 wire:loading-class.add="baz" wire:loading-target="bar"></h1>`
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('baz')).toBeTruthy()
        expect(document.querySelector('h1').classList.contains('baz')).toBeFalsy()
    })
})
