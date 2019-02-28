import { fireEventAndGetPayloadBeingSentToServer } from './utils'

test('can emit event directly to parent component with click', async () => {
    document.body.innerHTML = `
    <div wire:id="componentA" wire:serialized="component-a">
        <p>foo</p>

        <div wire:id="componentB" wire:serialized="component-b">
            <button wire:click="$emit('someEvent')">foo</button>
        </div>
    </div>
    `
    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.type).toEqual('fireEvent')
    expect(payload.id).toEqual('componentA')
    expect(payload.serialized).toEqual('component-a')
    expect(payload.data.childId).toEqual('componentB')
    expect(payload.data.name).toEqual('someEvent')
    expect(payload.data.params).toEqual([])
})

test('can emit event directly to parent component with keydown', async () => {
    document.body.innerHTML = `
    <div wire:id="componentA" wire:serialized="component-a">
        <p>foo</p>

        <div wire:id="componentB" wire:serialized="component-b">
            <input wire:keydown.enter="$emit('someEvent')">foo</input>
        </div>
    </div>
    `
    const payload = await fireEventAndGetPayloadBeingSentToServer('input', 'keydown', { key: "Enter" })

    expect(payload.type).toEqual('fireEvent')
    expect(payload.id).toEqual('componentA')
    expect(payload.serialized).toEqual('component-a')
    expect(payload.data.childId).toEqual('componentB')
    expect(payload.data.name).toEqual('someEvent')
    expect(payload.data.params).toEqual([])
})
