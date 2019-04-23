import { wait } from 'dom-testing-library'
import { mount } from './utils'

test('click inside nested component is assigned to nested component', async () => {
    var payload
    mount(`<div>
        <div wire:id="456" wire:serialized="{&quot;properties&quot;: {}}">
            <button wire:click="someMethod"></button>
        </div>
    </div>`, i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.id).toEqual('456')
    })
})
