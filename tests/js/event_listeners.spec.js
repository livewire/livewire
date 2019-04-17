import { wait } from 'dom-testing-library'
import { mountWithEvent } from './utils'

test('receive event from global fire', async () => {
    var payload
    mountWithEvent('<div></div>', ['foo'], i => payload = i)

    window.livewire.emit('foo', 'bar');

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('fireEvent')
        expect(payload.actionQueue[0].payload.event).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
    })
})

test('receive event from action fire', async () => {
    var payload
    mountWithEvent('<button wire:click="$emit(\'foo\', \'bar\')"></button>', ['foo'], i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('fireEvent')
        expect(payload.actionQueue[0].payload.event).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
    })
})
