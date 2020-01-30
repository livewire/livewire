import {wait} from 'dom-testing-library'
import Livewire from 'laravel-livewire'

test('data can be added to the query string', async () => {
    var newUri

    window.history.replaceState = (object, title, uri) => {
        newUri = uri
    }

    // This is a crude way of wiping any existing DOM & listeners before we mount.
    document.body.innerHTML = '';

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
                        updatesQueryString: ['foo'],
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
        expect(newUri).toEqual('/?foo=baz')
    })
})

test('data can be an object and still be passed to the query string', async () => {
    var newUri

    window.history.replaceState = (object, title, uri) => {
        newUri = uri
    }

    // This is a crude way of wiping any existing DOM & listeners before we mount.
    document.body.innerHTML = '';

    document.body.innerHTML = `
        <div wire:id="123" wire:initial-data="${JSON.stringify({
        bar: 'baz',
        foo: {
            bar: 'baz',
            baz: {
                foo: 'bar'
            }
        }
    }).replace(/\"/g, '&quot;')}">
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
                        data: {
                            bar: 'baz',
                            foo: {
                                bar: 'baz',
                                baz: {
                                    foo: 'bar'
                                }
                            }
                        },
                        updatesQueryString: ['bar', 'foo'],
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
        expect(newUri).toEqual('/?bar=baz&foo[bar]=baz&foo[baz][foo]=bar')
    })
})
