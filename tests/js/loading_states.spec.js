import { wait } from 'dom-testing-library'
import { mount } from './utils'

test('show element while loading', async () => {
    mount('<button wire:click="onClick"></button><span style="display: none" wire:loading></span>')

    expect(document.querySelector('span').style.display).toEqual('none')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')
    })
})

test('show element while targeted element is loading', async () => {
    mount(
`<button wire:ref="foo" wire:click="onClick"></button>
<span style="display: none" wire:loading wire:target="foo"></span>
<h1 style="display: none" wire:loading wire:target="bar"></h1>`
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')
        expect(document.querySelector('h1').style.display).toEqual('none')
    })
})

test('add element class while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.class="foo-class"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('foo-class')).toBeTruthy()
    })
})

test('remove element class while loading', async () => {
    mount('<button wire:click="onClick"></button><span class="hidden" wire:loading.class.remove="hidden"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('hidden')).toBeFalsy()
    })
})

test('add element attribute while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.attr="disabled"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').hasAttribute('disabled')).toBeTruthy()
    })
})

test('remove element attribute while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.attr.remove="disabled" disabled="true"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').hasAttribute('disabled')).toBeFalsy()
    })
})
