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

test('script tag is executed if conditionally rendered inside nested component', async () => {
    window.scriptTagWasCalled = false
    mountAndReturn(
        `<div><button wire:click="foo"></button></div>`,
        `<div>
        <button wire:click="foo"></button>
        <div wire:id="456" wire:initial-data="{}">
            <script>window.scriptTagWasCalled = true</script>
        </div>
    </div>`
    )

    expect(window.scriptTagWasCalled).toBeFalsy()

    document.querySelector('button').click()

    await wait(async () => {
        expect(window.scriptTagWasCalled).toBeTruthy()
    })
})
