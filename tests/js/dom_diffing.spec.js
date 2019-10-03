import { fireEvent, wait } from 'dom-testing-library'
import { mountAsRootAndReturn } from './utils'
const timeout = ms => new Promise(resolve => setTimeout(resolve, ms))

test('element root is DOM diffed', async () => {
    mountAsRootAndReturn(
        '<div wire:id="123" wire:data="{}"><button wire:click="$refresh"></button></div>',
        '<div wire:id="123" wire:data="{}" class="bar"><button wire:click="$refresh"></button></div>'
    )

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(document.querySelector('div').classList.contains('bar')).toBeTruthy()
    })
})

test('element inserted in the middle moves subsequent elements instead of removing them', async () => {
    var hookWasCalled = false

    mountAsRootAndReturn(
        '<div wire:id="123" wire:data="{}"><button wire:click="$refresh"></button><p>there</p></div>',
        '<div wire:id="123" wire:data="{}" class="bar"><button wire:click="$refresh"></button><div>middle</div><p>there</p></div>'
    )

    window.livewire.hook('elementRemoved', () => {
        hookWasCalled = true
    })

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(document.querySelector('div').classList.contains('bar')).toBeTruthy()
        expect(hookWasCalled).toBeFalsy()
    })
})

test('element inserted before element with same tag name is handled as if they were different.', async () => {
    var elThatWasAdded

    mountAsRootAndReturn(
        '<div wire:id="123" wire:data="{}"><button wire:click="$refresh"></button><div>there</div></div>',
        '<div wire:id="123" wire:data="{}" class="bar"><button wire:click="$refresh"></button><div>hey</div><div>there</div></div></div>'
    )

    window.livewire.hook('elementInitialized', (el) => {
        elThatWasAdded = el
    })

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(document.querySelector('div').classList.contains('bar')).toBeTruthy()
        expect(elThatWasAdded.el.innerHTML).toEqual('hey')
    })
})
