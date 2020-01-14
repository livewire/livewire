import { wait } from 'dom-testing-library'
import Livewire from 'laravel-livewire'

test('can add custom directives', async () => {
    document.body.innerHTML = `
        <div wire:id="123" wire:initial-data="{}">
            <button wire:foo.bar="baz('bob', 'lob')"></button>
        </div>
    `

    var payload
    window.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(i) {
            payload = i
        },
    }})

    window.livewire.directive('foo', (el, directive, component) => {
        el.addEventListener('click', () => {
            component.call('foo', directive.modifiers[0], directive.method, directive.params)
        })
    })

    window.livewire.start()

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual([
            'bar',
            'baz',
            ['bob', 'lob'],
        ])
    })
})
