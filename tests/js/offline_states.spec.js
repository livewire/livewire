import { mount } from './utils'
import { dispatch } from './../../src/js/util'

test('element is toggled when going offline and back online', async () => {
    mount(`<span wire:offline style="display: none"></span>`)

    expect(document.querySelector('span').style.display).toEqual('none')

    dispatch('offline', { target: window })

    expect(document.querySelector('span').style.display).toEqual('inline-block')

    dispatch('online', { target: window })

    expect(document.querySelector('span').style.display).toEqual('none')
})

test('add element class while offline', async () => {
    mount('<span wire:offline.class="foo"></span>')

    dispatch('offline', { target: window })

    expect(document.querySelector('span').classList.contains('foo')).toBeTruthy()
})

test('remove element class while offline', async () => {
    mount('<span class="hidden" wire:offline.class.remove="hidden"></span>')

    dispatch('offline', { target: window })

    expect(document.querySelector('span').classList.contains('hidden')).toBeFalsy()
})

test('add element attribute while offline', async () => {
    mount('<span wire:offline.attr="disabled"></span>')

    dispatch('offline', { target: window })

    expect(document.querySelector('span').hasAttribute('disabled')).toBeTruthy()
})

test('remove element attribute while offline', async () => {
    mount('<span wire:offline.attr.remove="disabled" disabled="true"></span>')

    dispatch('offline', { target: window })

    expect(document.querySelector('span').hasAttribute('disabled')).toBeFalsy()
})
