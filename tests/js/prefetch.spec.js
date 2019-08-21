import { fireEvent, wait } from 'dom-testing-library'
import { mountAndReturn } from './utils'

test('DOM result is prefetched on mouseover', async () => {
    var payload
    mountAndReturn(
        '<button wire:click.prefetch="onPrefetch"></button>',
        '<div id="foo"></div>',
        [], i => payload = i
    )

    fireEvent.mouseEnter(document.querySelector('button'))

    await wait(async () => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('onPrefetch')
        expect(payload.actionQueue[0].payload.params).toEqual([])
        expect(payload.fromPrefetch).toBeTruthy()
        expect(document.querySelector('#foo')).toBeFalsy()

        fireEvent.click(document.querySelector('button'))

        await wait(() => {
            expect(document.querySelector('#foo')).toBeTruthy()
        })
    })
})
