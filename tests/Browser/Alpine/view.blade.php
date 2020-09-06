<div>
    <div x-data="{ output: '' }" x-on:some-event.window="output = $event.detail">
        <span x-text="output" dusk="foo.output"></span>
        <button type="button" dusk="foo.button" wire:click="dispatchSomeEvent">Dispatch</button>
    </div>

    <div x-data="{ count: 0 }">
        <span x-text="count" dusk="bar.output"></span>
        <button type="button" dusk="bar.button" x-on:click="count++">Inc</button>
        <button type="button" dusk="bar.refresh" wire:click="$refresh">Refresh</button>
    </div>

    <div x-data>
        <span dusk="baz.get" x-text="@this.get('count')"></span>
        <span dusk="baz.get.proxy" x-text="$wire.get('count')"></span>
        <span dusk="baz.get.proxy.magic" x-text="$wire.count"></span>

        <button type="button" dusk="baz.set" x-on:click="@this.set('count', 1)"></button>
        <button type="button" dusk="baz.set.proxy" x-on:click="$wire.set('count', 2)"></button>
        <button type="button" dusk="baz.set.proxy.magic" x-on:click="$wire.count++"></button>

        <button type="button" dusk="baz.call" x-on:click="@this.call('setCount', 4)"></button>
        <button type="button" dusk="baz.call.proxy" x-on:click="$wire.call('setCount', 5)"></button>
        <button type="button" dusk="baz.call.proxy.magic" x-on:click="$wire.setCount(6)"></button>

        <span dusk="baz.output">{{ $count }}</span>
    </div>

    <div x-data="{ count: null }">
        <button type="button" dusk="bob.button.await" x-on:click="count = await $wire.returnValue(1)"></button>
        <button type="button" dusk="bob.button.promise" x-on:click="$wire.returnValue(2).then(value => count = value)"></button>

        <span dusk="bob.output" x-text="count"></span>
    </div>

    <div x-data="{ count: @entangle('count') }">
        <button wire:click="$set('count', 100)" dusk="lob.reset">Reset</button>
        <button type="button" dusk="lob.increment" x-on:click="count++"></button>
        <button type="button" dusk="lob.decrement" x-on:click="$wire.count--"></button>

        <span dusk="lob.output" x-text="$wire.count"></span>
    </div>
</div>
