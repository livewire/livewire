<div>
    <input type="checkbox" wire:model="foo" dusk="foo"><span dusk="foo.output">@json($foo)</span>

    <input type="checkbox" wire:model="bar" value="a" dusk="bar.a">
    <input type="checkbox" wire:model="bar" value="b" dusk="bar.b">
    <input type="checkbox" wire:model="bar" value="c" dusk="bar.c">
    <span dusk="bar.output">@json($bar)</span>

    <input type="checkbox" wire:model="baz" dusk="baz" value="2">
</div>
