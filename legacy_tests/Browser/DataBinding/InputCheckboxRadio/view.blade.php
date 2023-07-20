<div>
    <input type="checkbox" wire:model.live="foo" dusk="foo"><span dusk="foo.output">@json($foo)</span>

    <input type="checkbox" wire:model.live="bar" value="a" dusk="bar.a">
    <input type="checkbox" wire:model.live="bar" value="b" dusk="bar.b">
    <input type="checkbox" wire:model.live="bar" value="c" dusk="bar.c">
    <span dusk="bar.output">@json($bar)</span>

    <input type="checkbox" wire:model.live="baz" dusk="baz" value="2">
</div>
