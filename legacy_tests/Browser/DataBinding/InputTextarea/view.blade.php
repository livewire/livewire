<div>
    <textarea wire:model.live="foo" dusk="foo" class="{{ $showFooClass ? 'foo' : '' }}"></textarea><span dusk="foo.output">{{ $foo }}</span>
    <button wire:click="updateFooTo('changed')" dusk="foo.change">Change Foo</button>
    <button wire:click="$set('showFooClass', true)" dusk="foo.add-class">Add Class</button>

    <textarea wire:model.blur="baz" dusk="baz"></textarea><span dusk="baz.output">{{ $baz }}</span>

    <textarea wire:model="bob" dusk="bob"></textarea><span dusk="bob.output">{{ $bob }}</span>
    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
</div>
