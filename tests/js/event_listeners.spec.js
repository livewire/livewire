import { wait } from 'dom-testing-library'
import { mountWithEvent } from './utils'

test('fire and receive event', async () => {
    var payload
    mountWithEvent('<div></div>', ['foo'], i => payload = i)

    document.livewire.emit('foo', 'bar');

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('fireEvent')
        expect(payload.actionQueue[0].payload.event).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
    })
})
