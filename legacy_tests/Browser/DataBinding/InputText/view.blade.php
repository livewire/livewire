<div>
    <input type="text" wire:model.live="foo" dusk="foo"><span dusk="foo.output">{{ $foo }}</span>
    <button wire:click="updateFooTo('changed')" dusk="foo.change">Change Foo</button>

    <input type="text" wire:model.live="bar.baz.bob" dusk="bar"><span dusk="bar.output">@json($bar)</span>

    <input type="text" wire:model.lazy="baz" dusk="baz"><span dusk="baz.output">{{ $baz }}</span>

    <input type="text" wire:model="bob" dusk="bob"><span dusk="bob.output">{{ $bob }}</span>
    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
</div>
