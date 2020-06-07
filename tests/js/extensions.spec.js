import { wait } from 'dom-testing-library'
import Livewire from 'laravel-livewire'
import testHarness from './fixtures/test_harness'

test('can add custom directives', async () => {
    let dom = `
        <div wire:id="123" wire:initial-data="{}">
            <button wire:foo.bar="baz('bob', 'lob')"></button>
        </div>
    `

    var payload
    testHarness.mount({
        dom,
        asRoot: true,
        requestInterceptor: i => payload = i,
        directives: [{
            name: 'foo',
            callback: (el, directive, component) => {
                el.addEventListener('click', () => {
                    component.call('foo', directive.modifiers[0], directive.method, directive.params)
                })
            }
        }]
    })

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
