<div>
    <input type="text" wire:model.defer="foo" dusk="foo">
    <button type="button" dusk="foo.output">{{ $foo }}</button>

    <input type="checkbox" wire:model.defer="bar" value="a" dusk="bar.a">
    <input type="checkbox" wire:model.defer="bar" value="b" dusk="bar.b">
    <button type="button" dusk="bar.output">@json($bar)</button>

    <button wire:click="$refresh" dusk="refresh">Refresh</button>
</div>
