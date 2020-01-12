import { wait } from 'dom-testing-library'
import { mountAndReturnDispatchedEvent } from './utils'

test('receive event from global fire', async () => {
    mountAndReturnDispatchedEvent(
        '<div><button wire:click="$refresh"></button></div>',
        {event: 'foo', data: {bar: 'baz'}},
    )

    var eventReceived;
    window.addEventListener('foo', e => {
        eventReceived = e
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(eventReceived.type).toEqual('foo')
        expect(eventReceived.detail.bar).toEqual('baz')
    })
})
