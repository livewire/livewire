import { fireEventAndGetPayloadBeingSentToServer } from './utils'

test('can emit event directly to parent component', async () => {
    document.body.innerHTML = `
    <div wire:root-id="componentA" wire:root-serialized="component-a">
        <p>foo</p>

        <div wire:root-id="componentB" wire:root-serialized="component-b">
            <button wire:click="$emit('someEvent')">foo</button>
        </div>
    </div>
    `
    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.event).toEqual('fireEvent')
    expect(payload.id).toEqual('componentA')
    expect(payload.serialized).toEqual('component-a')
    expect(payload.data.childId).toEqual('componentB')
    expect(payload.data.name).toEqual('someEvent')
    expect(payload.data.params).toEqual([])
})
