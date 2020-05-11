import { wait } from 'dom-testing-library'
import { mountAndReturn } from './utils'

test('script tag is executed if conditionally rendered', async () => {
    window.scriptTagWasCalled = false
    mountAndReturn(
        '<button wire:click="$refresh"></button>',
        '<button wire:click="$refresh"></button><script>window.scriptTagWasCalled = true</script>'
    )

    expect(window.scriptTagWasCalled).toBeFalsy()

    document.querySelector('button').click()

    await wait(async () => {
        expect(window.scriptTagWasCalled).toBeTruthy()
    })
})
