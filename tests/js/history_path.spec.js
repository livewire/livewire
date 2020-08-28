import {wait} from 'dom-testing-library'
import Livewire from 'laravel-livewire'

test('data can be added to the query string', async () => {
    var newUri
    var newData

    window.history.replaceState = (object, title, uri) => {
        newUri = uri
        newData = object.livewire
    }

    document.body.innerHTML = `
        <div wire:id="123" wire:initial-data="${JSON.stringify({foo: 'bar'}).replace(/\"/g, '&quot;')}">
            <button wire:click="$refresh"></button>
        </div>
    `

    window.livewire = new Livewire({
        driver: {
            onMessage: null,
            init() {
            },
            async sendMessage(payload) {
                setTimeout(() => {
                    this.onMessage({
                        fromPrefetch: payload.fromPrefetch,
                        id: payload.id,
                        data: {foo: 'baz'},
                        historyPath: '/foo/baz',
                        dirtyInputs: [],
                        dom: '<div wire:id="123"><button wire:click="$refresh"></button></div>',
                    })
                }, 1)
            },
        }
    })
    window.livewire.start()

    document.querySelector('button').click()

    await wait(async () => {
        expect(newUri).toEqual('/foo/baz')
        expect(newData).toEqual({foo: 'baz'})
    })
})
