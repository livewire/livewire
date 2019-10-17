import { fireEvent, wait } from 'dom-testing-library'
import { mountAndReturn } from './utils'

test('nested component is garbage collected when its removed from the DOM', async () => {
    var payload
    mountAndReturn(
        `<div>
            <button wire:click="$refresh"></button>
            <div wire:id="456" wire:initial-data="{}">
                <p>Nested Component</p>
            </div>
        </div>`,
        '<div><button wire:click="$refresh"></button></div>',
        [], i => payload = i
    )

    // No component is in need of garbage collecting yet.
    expect(window.livewire.components.getComponentsForCollection()).toEqual([])

    fireEvent.click(document.querySelector('button'))

    await wait(async () => {
        // We are hearing back from the server that the component has been removed.
        expect(payload.gc).toEqual([])
        // We can see that reflected in the store after morphdom removed the
        // component's root element and added it's id to this queue.
        expect(window.livewire.components.getComponentsForCollection()).toEqual(['456'])

        // This click should be the one to send off the component id to be collected.
        fireEvent.click(document.querySelector('button'))

        await wait(async () => {
            // We heard back from the server that the component id has been collected.
            expect(payload.gc).toEqual(['456'])
            // Now we told the queue that it has been collected on the server and we can
            // be done tracking it.
            expect(window.livewire.components.getComponentsForCollection()).toEqual([])
        })
    })
})
