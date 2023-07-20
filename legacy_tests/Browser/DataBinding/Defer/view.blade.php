<div>
    <input type="text" wire:model="foo" dusk="foo">
    <button type="button" dusk="foo.output">{{ $foo }}</button>

    <input type="checkbox" wire:model="bar" value="a" dusk="bar.a">
    <input type="checkbox" wire:model="bar" value="b" dusk="bar.b">
    <button type="button" dusk="bar.output">@json($bar)</button>

    <button wire:click="$refresh" dusk="refresh">Refresh</button>
</div>
