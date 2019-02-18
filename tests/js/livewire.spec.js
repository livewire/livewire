import http from 'http'
http.sendMessage = jest.fn(() => {
    this.onMessage('hey')
})
import livewire from 'livewire'
import simulant from 'jsdom-simulant'

const mockData = { data: {
    id: 'test-id',
    dom: `
<div wire:root-id="test-id" wire:root-serialized="test-serialized">
    <button wire:click="doSomething">Hey Hey</button>
    <button wire:click="doSomethingElse">hey there</button>
</div>`,
    dirtyInputs: [],
    serialized: 'yeaya',
}};

document.body.innerHTML = `
<div wire:root-id="test-id" wire:root-serialized="test-serialized">
    <button wire:click="doSomething"></button>
</div>
`

test('adds 1 + 2 to equal 3', async (done) => {
    jest.useFakeTimers()

    livewire.init()

    console.log(document.querySelector('div').outerHTML)
    simulant.fire(document.querySelector('button'), 'click')
    jest.runAllTimers();

    setTimeout(() => {
        // console.log(document.querySelector('div').innerHTML)
        console.log(document.querySelector('div').outerHTML)
        done()
    })
});
