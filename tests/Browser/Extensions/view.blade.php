<div>
    <button wire:click="$refresh" dusk="refresh">refresh</button>
    @if ($count > 1)
        <button wire:foo>foo</button>
    @endif
</div>
