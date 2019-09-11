import { fireEvent, wait } from 'dom-testing-library'
import { mountAsRootAndReturn } from './utils'

test('Element root is DOM diffed', async () => {
    mountAsRootAndReturn(
        '<div wire:id="123" wire:data="{}"><button wire:click="$refresh"></button></div>',
        '<div wire:id="123" wire:data="{}" class="bar"><button wire:click="$refresh"></button></div>'
    )

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(document.querySelector('div').classList.contains('bar')).toBeTruthy()
    })
})
