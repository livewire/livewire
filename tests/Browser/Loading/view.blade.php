<div>
    <button wire:click="$refresh" dusk="button">Load</button>

    <h1 wire:loading dusk="show">Loading...</h1>
    <h1 wire:loading.remove dusk="hide">Loading...</h1>

    <h1 wire:loading.class="foo" dusk="add-class">Loading...</h1>
    <h1 wire:loading.class.remove="foo" dusk="remove-class" class="foo">Loading...</h1>

    <h1 wire:loading.attr="disabled" dusk="add-attr">Loading...</h1>
    <h1 wire:loading.attr.remove="disabled" dusk="remove-attr" disabled>Loading...</h1>

    <h1 wire:loading wire:target="foo" dusk="targeting">Loading...</h1>

    <button wire:click="foo" dusk="target-button">targeted button</button>
</div>
