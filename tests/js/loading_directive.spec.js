import { fireEventAndExecuteCallbackWhileWaitingForServerToRespondWithDom } from './utils'

test('test loading', () => {
    document.body.innerHTML = `<div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <form wire:submit="doSomething" wire:ref="submitEl">
            <div id="spinner" class="hidden" wire:loading="submitEl"></div>
            <button></button>
        </form>
    </div>
    `
    expect(document.querySelector('#spinner').classList).toContain('hidden')

    fireEventAndExecuteCallbackWhileWaitingForServerToRespondWithDom('button', 'click', () => {
        expect(document.querySelector('#spinner').classList).not.toContain('hidden')
    }, document.body.innerHTML)

    expect(document.querySelector('#spinner').classList).toContain('hidden')
})
